<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_SigninimageController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->signinImageForm)) {
            $this->view->signinImageForm = $appSession->signinImageForm;
            unset($appSession->signinImageForm);
        } else {
            $this->view->signinImageForm = new Users_Form_SigninImage();
        }

        if (@$_COOKIE['image']) {
            $this->view->enabled = true;
        } else {
            $this->view->enabled = false;
        }

        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function saveimageAction()
    {
        $form = new Users_Form_SigninImage();
        $formData = $this->_request->getPost();

        // the framework doesn't allow doing this cleanly yet
        $formData = array_merge($formData, array('image' => $_FILES['image']['name']));

        $form->populate($formData);
        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->signinImageForm = $form;

            $this->_forward('index');
            return;
        }

        $fileInfo = $form->image->getFileInfo();
        $images = new Users_Model_SigninImages();
        $images->deleteForUser($this->user);
        $image = $images->createRow();
        $image->user_id = $this->user->id;
        $image->image = file_get_contents($fileInfo['image']['tmp_name']);
        $image->mime = $fileInfo['image']['type'];
        $image->cookie = $images->generateCookieId($this->user);
        $image->save();

        // delete cookie
        setcookie('image', $image->cookie, time() - 3600, '/', $this->_getCookieDomain());

        $this->_redirect('/users/signinimage');
    }

    public function setcookieAction()
    {
        if ($this->_request->getParam('enable')) {
            $images = new Users_Model_SigninImages();
            if (!$image = $images->getForUser($this->user)) {
                $this->_helper->FlashMessenger->addMessage($this->view->translate('There is no image uploaded'));
                $this->_redirect('/users/signinimage');
                return;
            }

            if (!setcookie('image', $image->cookie, time() + 24*60*60*10000, '/', $this->_getCookieDomain())) {
                $this->_helper->FlashMessenger->addMessage($this->view->translate('There was a problem setting the cookie'));
                $this->_redirect('/users/signinimage');
                return;
            }

            $this->_helper->FlashMessenger->addMessage($this->view->translate('Image has been set successfully on this computer/browser'));
        } else {
            setcookie('image', $image->cookie, time() - 3600, '/', $this->_getCookieDomain());

            $this->_helper->FlashMessenger->addMessage($this->view->translate('Image has been disabled successfully on this computer/browser'));
        }

        $this->_redirect('/users/signinimage');
    }

    public function imageAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);
        $this->_helper->layout->disableLayout();

        $images = new Users_Model_SigninImages();

        if ($cookie = $this->_request->getParam('id')) {
            $image = $images->getByCookie($cookie);
        } else if ($this->user->role != Users_Model_User::ROLE_GUEST) {
            $image = $images->getForUser($this->user);
        } else {
            return;
        }

        $this->_response->setHeader('Content-type', $image->mime);
        echo $image->image;
    }

    private function _getCookieDomain()
    {
        if ($this->_config->subdomain->enabled) {
            $domain = '.' . $this->_config->subdomain->hostname;
        } else {
            $domain = $_SERVER['HTTP_HOST'];
        }
    }
}
