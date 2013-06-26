<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

/**
* We don't use the session with the login form to simplify the dynamic appearance of the captcha
*/
class Users_LoginController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        $settings = new Model_Settings();
        $this->view->maintenanceEnabled = $settings->isMaintenanceMode();

        $authAttempts = new Users_Model_AuthAttempts();
        $attempt = $authAttempts->get();
        $this->view->useCaptcha = $attempt && $attempt->surpassedMaxAllowed();
        $this->view->loginForm = new Users_Form_Login(null, $this->view->base, $this->view->useCaptcha);

        if ($this->_config->SSL->enable_mixed_mode) {
            if ($this->_config->subdomain->enabled) {
                // in this case $this->view->base contains the full URL, so we just gotta replace the protocol
                $this->view->loginTargetBase = 'https' . substr($this->view->base, strpos($this->view->base, '://'));
            } else {
                $this->view->loginTargetBase = 'https://' . $_SERVER['HTTP_HOST'] . $this->view->base;
            }
        } else {
            $this->view->loginTargetBase = $this->view->base;
        }

        $this->view->allowRegistrations = $this->_config->environment->registrations_enabled;


        if ($this->user->role == Users_Model_User::ROLE_GUEST && @$_COOKIE['image']) {
            $images = new Users_Model_SigninImages();
            $this->view->image = $images->getByCookie($_COOKIE['image']);
        } else {
            $this->view->image = false;
        }

        $this->view->yubikey = $this->_config->yubikey;

        $this->_helper->viewRenderer->setResponseSegment('sidebar');
    }

    public function authenticateAction()
    {
        $authAttempts = new Users_Model_AuthAttempts();
        $attempt = $authAttempts->get();

        $form = new Users_Form_Login(null, $this->view->base, $attempt && $attempt->surpassedMaxAllowed());
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Invalid credentials'));
            $this->_redirectToNormalConnection('');
        }

        $users = new Users_Model_Users();
        $result = $users->authenticate(
            $this->_request->getPost('username'),
            $this->_config->yubikey->enabled && $this->_config->yubikey->force?
                $this->_request->getPost('yubikey')
                : $this->_request->getPost('password'),
            false,
            $this->view
        );
       
        if ($result) {
            $user = $users->getUser();

            if ($attempt) {
                $attempt = $authAttempts->delete();
            }
            
            if ($user->role != Users_Model_User::ROLE_ADMIN && $this->underMaintenance) {
                Zend_Auth::getInstance()->clearIdentity();

                return $this->_redirectForMaintenance(true);
            }
        } else {
            if (!$attempt) {
                $authAttempts->create();
            } else {
                $attempt->addFailure();
                $attempt->save();
            }

            $this->_helper->FlashMessenger->addMessage($this->view->translate('Invalid credentials'));
        }

        $this->_redirectToNormalConnection('');
    }

    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::forgetMe();

        $this->_redirect('');
    }
}
