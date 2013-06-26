<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_RecoverpasswordController extends CommunityID_Controller_Action
{
    public function init()
    {
        parent::init();

        if ($this->user->role != Users_Model_User::ROLE_ADMIN && $this->underMaintenance) {
            return $this->_redirectForMaintenance();
        }
    }

    public function indexAction()
    {
        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->recoverPasswordForm)) {
            $this->view->form = $appSession->recoverPasswordForm;
            unset($appSession->recoverPasswordForm);
        } else {
            $this->view->form = new Users_Form_RecoverPassword();
        }

        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function sendAction()
    {
        $form = new Users_Form_RecoverPassword();
        $formData = $this->_request->getPost();

        $form->populate($formData);
        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->recoverPasswordForm = $form;
            return $this->_forward('index');
        }

        $users = new Users_Model_Users();
        $user = $users->getUserWithEmail($form->getValue('email'));
        if (!$user) {
            $form->email->addError($this->view->translate('This E-mail is not registered in the system'));
            $appSession = Zend_Registry::get('appSession');
            $appSession->recoverPasswordForm = $form;
            return $this->_forward('index');
        }

        $user->token = Users_Model_User::generateToken();
        $user->save();

        $file = CommunityID_Resources::getResourcePath('passwordreset_mail.txt');
        $emailTemplate = file_get_contents($file);
        $emailTemplate = str_replace('{userName}', $user->getFullName(), $emailTemplate);
        $emailTemplate = str_replace('{IP}', $_SERVER['REMOTE_ADDR'], $emailTemplate);

        // $_SERVER['SCRIPT_URI'] is not always available
        $URI = self::getProtocol() . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        preg_match('#(.*)/users/recoverpassword#', $URI, $matches);
        $emailTemplate = str_replace('{passwordResetURL}',
                                     $matches[1] . '/users/recoverpassword/reset?token=' . $user->token,
                                     $emailTemplate);
        
        $this->_sendMail($user->email, $this->view->translate('Community-ID password reset'), $emailTemplate);

        $this->_helper->FlashMessenger->addMessage($this->view->translate('Password reset E-mail has been sent'));
        $this->_redirect('');
    }

    public function resetAction()
    {
        $users = new Users_Model_Users();
        $user = $users->getUserWithToken($this->_getParam('token'));
        if (!$user) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Wrong Token'));
            $this->_redirect('');
            return;
        }

        $newPassword = $user->generateRandomPassword();
        $user->setClearPassword($newPassword);

        // reset token
        $user->token = Users_Model_User::generateToken();

        $user->save();

        $file = CommunityID_Resources::getResourcePath('passwordreset2_mail.txt');
        $emailTemplate = file_get_contents($file);
        $emailTemplate = str_replace('{userName}', $user->getFullName(), $emailTemplate);
        $emailTemplate = str_replace('{password}', $newPassword, $emailTemplate);

        $this->_sendMail($user->email, $this->view->translate('Community-ID password reset'), $emailTemplate);

        $this->_helper->FlashMessenger->addMessage($this->view->translate('You\'ll receive your new password via E-mail'));
        $this->_redirect('');
    }

    private function _sendMail($to, $subject, $body)
    {
        if (strtolower($this->_config->email->transport) == 'smtp') {
            Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Smtp($this->_config->email->host, $this->_config->email->toArray()));
        } else {
            Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Sendmail());
        }
        $mail = new Zend_Mail('utf-8');
        $mail->setBodyText($body);
        $mail->setFrom($this->_config->email->supportemail);
        $mail->addTo($to);
        $mail->setSubject($subject);
        $mail->send();
    }
}
