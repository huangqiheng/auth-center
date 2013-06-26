<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_RegisterController extends CommunityID_Controller_Action
{
    protected $_numCols = 1;

    public function init()
    {
        parent::init();

        if ($this->user->role != Users_Model_User::ROLE_ADMIN && $this->underMaintenance) {
            return $this->_redirectForMaintenance();
        }

        if (!$this->_config->environment->registrations_enabled) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate(
                'Sorry, registrations are currently disabled'
            ));
            return $this->_redirect('');
        }
    }

    public function indexAction()
    {
        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->registerForm)) {
            $form = $appSession->registerForm;
            unset($appSession->registerForm);
        } else {
            $form = new Users_Form_Register(null, $this->view->base);
        }
        $this->view->form = $form;
    }
    
    public function saveAction()
    {
        $form = new Users_Form_Register(null, $this->view->base);
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->registerForm = $form;
            return $this->_forward('index', null, null);
        }

        $users = new Users_Model_Users();

        if ($users->getUserWithUsername($form->getValue('username'), false, $this->view)) {
            $form->username->addError($this->view->translate('This username is already in use'));
            $appSession = Zend_Registry::get('appSession');
            $appSession->registerForm = $form;
            return $this->_forward('index', null, null);
        }

        if ($users->getUserWithEmail($form->getValue('email'))) {
            $form->email->addError($this->view->translate('This E-mail is already in use'));
            $appSession = Zend_Registry::get('appSession');
            $appSession->registerForm = $form;
            return $this->_forward('index', null, null);
        }

        $user = $users->createRow();

        $user->firstname = $form->getValue('firstname');
        $user->lastname = $form->getValue('lastname');
        $user->email = $form->getValue('email');
        $user->username = $form->getValue('username');

        preg_match('#(.*)/users/register/save#', Zend_OpenId::selfURL(), $matches);
        $user->generateOpenId($matches[1]);

        if ($this->_config->ldap->enabled) {
            // when using ldap, unconfirmed users' password is saved unhashed temporarily, while he registers,
            // and then it's stored in LDAP and cleared from the db
            $user->setPassword($form->getValue('password1'));
        } else {
            $user->setClearPassword($form->getValue('password1'));
        }

        $user->role = Users_Model_User::ROLE_GUEST;
        $user->token = Users_Model_User::generateToken();
        $user->accepted_eula = 0;
        $user->registration_date = date('Y-m-d');

        $mail = self::getMail($user, $this->view->translate('Community-ID registration confirmation'));
        try {
            $mail->send();
            $user->save();
            $user->createDefaultProfile($this->view);
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Thank you.'));
            $this->_helper->FlashMessenger->addMessage($this->view->translate('You will receive an E-mail with instructions to activate the account.'));
        } catch (Zend_Mail_Exception $e) {
            if ($this->_config->environment->production) {
                $this->_helper->FlashMessenger->addMessage($this->view->translate('The confirmation E-mail could not be sent, so the account creation was cancelled. Please contact support.'));
            } else {
                $this->_helper->FlashMessenger->addMessage($this->view->translate('The account was created but the E-mail could not be sent'));
                // I still wanna create the user when in development mode
                $user->save();
            }
            if ($this->_config->logging->level == Zend_Log::DEBUG) {
                $this->_helper->FlashMessenger->addMessage($e->getMessage());
            }
        }

        $this->_redirect('');
    }

    public function eulaAction()
    {
        $users = new Users_Model_Users();
        if ($this->_request->getParam('token') == ''
                || !($user = $users->getUserWithToken($this->_request->getParam('token')))) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Invalid token'));
            $this->_redirect('');
            return;
        }

        $this->view->token = $user->token;

        $file = CommunityID_Resources::getResourcePath('eula.txt');
        $this->view->eula = file_get_contents($file);
    }

    public function declineeulaAction()
    {
        $users = new Users_Model_Users();

        if ($this->_request->getParam('token') == ''
                || !($user = $users->getUserWithToken($this->_request->getParam('token')))) {
            Zend_Registry::get('logger')->log('invalid token', Zend_Log::DEBUG);
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Invalid token'));
            $this->_redirect('');
            return;
        }

        $user->delete();
        $this->_helper->FlashMessenger->addMessage($this->view->translate('Your account has been deleted'));
        $this->_redirect('');
    }

    public function accepteulaAction()
    {
        $users = new Users_Model_Users();
        if ($this->_request->getParam('token') == ''
                || !($user = $users->getUserWithToken($this->_request->getParam('token')))) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Invalid token'));
            $this->_redirect('');
            return;
        }

        $user->role = Users_Model_User::ROLE_REGISTERED;
        $user->accepted_eula = 1;
        $user->registration_date = date('Y-m-d');
        $user->token = '';

        if ($this->_config->ldap->enabled) {
            $ldap = Monkeys_Ldap::getInstance();
            $ldap->add($user);

            // clear unencrypted password
            $user->setPassword('');
        }

        $user->save();

        $auth = Zend_Auth::getInstance();
        $auth->getStorage()->write($user);

        $this->_redirect('/users/profile');
    }

    /**
    * @return Zend_Mail
    * @throws Zend_Mail_Protocol_Exception
    */
    public static function getMail(Users_Model_User $user, $subject)
    {
        $file = CommunityID_Resources::getResourcePath('registration_mail.txt');
        $emailTemplate = file_get_contents($file);
        $emailTemplate = str_replace('{userName}', $user->getFullName(), $emailTemplate);

        $currentUrl = Zend_OpenId::selfURL();
        preg_match('#(.*)/register/save#', $currentUrl, $matches);
        $emailTemplate = str_replace('{registrationURL}', $matches[1] . '/register/eula?token=' . $user->token, $emailTemplate);

        // can't use $this-_config 'cause it's a static function
        $configEmail = Zend_Registry::get('config')->email;

        switch (strtolower($configEmail->transport)) {
            case 'smtp':
                Zend_Mail::setDefaultTransport(
                    new Zend_Mail_Transport_Smtp(
                        $configEmail->host,
                        $configEmail->toArray()
                    )
                );
                break;
            case 'mock':
                Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Mock());
                break;
            default:
                Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Sendmail());
        }

        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyText($emailTemplate);
        $mail->setFrom($configEmail->supportemail);
        $mail->addTo($user->email);
        $mail->setSubject($subject);

        return $mail;
    }
}
