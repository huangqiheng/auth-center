<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class OpenidController extends CommunityID_Controller_Action
{
    protected $_numCols = 1;

    public function providerAction()
    {
        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();
        $sites = new Model_Sites();

        if (!$request) {
            $this->_helper->viewRenderer->setNeverRender(true);
            $this->_response->setRawHeader('HTTP/1.0 403 Forbidden');
            Zend_Registry::get('logger')->log("OpenIdController::providerAction: FORBIDDEN", Zend_Log::DEBUG);
            echo $this->view->translate('Forbidden');
            return;
        }

        // association and other transactions, handled automatically by the framework
        if (!in_array($request->mode, array('checkid_immediate', 'checkid_setup'))) {
            return $this->_sendResponse($server, $server->handleRequest($request));
        }

        // can't process immediate requests if user is not logged in
        if ($request->immediate && $this->user->role == Users_Model_User::ROLE_GUEST) {
            return $this->_sendResponse($server, $request->answer(false));
        }

        $trustRoot = $this->_getTrustRoot($request);

        if ($request->idSelect()) {
            if ($this->user->role == Users_Model_User::ROLE_GUEST) {
                $this->_forward('login');
            } else {
                if ($sites->isTrusted($this->user, $trustRoot)) {
                    $this->_forward('proceed', null, null, array('allow' => true));
                } elseif ($sites->isNeverTrusted($this->user, $trustRoot)) {
                    $this->_forward('proceed', null, null, array('allow' => false));
                } else {
                    if ($request->immediate) {
                        return $this->_sendResponse($server, $request->answer(false));
                    }

                    $this->_forward('trust');
                }
            }
        } else {
            if (!$request->identity) {
                die('No identifier sent by OpenID relay');
            }

            if ($this->user->role == Users_Model_User::ROLE_GUEST) {
                $this->_forward('login');
            } else {
                // user is logged-in already. Check the requested identity is his
                if ($this->user->openid != $request->identity) {
                    Zend_Auth::getInstance()->clearIdentity();
                    if ($this->immediate) {
                        return $this->_sendResponse($server, $request->answer(false));
                    }

                    $this->_forward('login');
                    return;
                }

                // Check if max_auth_age is requested through the PAPE extension
                require_once 'libs/Auth/OpenID/PAPE.php';
                if ($papeRequest = Auth_OpenID_PAPE_Request::fromOpenIDRequest($request)) {
                    $extensionArgs = $papeRequest->getExtensionArgs();
                    if (isset($extensionArgs['max_auth_age'])
                            && $extensionArgs['max_auth_age'] < $this->user->getSecondsSinceLastLogin())
                    {
                        $this->_forward('login');
                        return;
                    }
                }

                if ($sites->isTrusted($this->user, $trustRoot)) {
                    $this->_forward('proceed', null, null, array('allow' => true));
                } elseif ($sites->isNeverTrusted($this->user, $trustRoot)) {
                    $this->_forward('proceed', null, null, array('deny' => true));
                } else {
                    $this->_forward('trust');
                }
            }
        }
    }

    /**
    * We don't use the session with the login form to simplify the dynamic appearance of the captcha
    */
    public function loginAction()
    {
        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();

        $this->view->yubikey = $this->_config->yubikey;

        $authAttempts = new Users_Model_AuthAttempts();
        $attempt = $authAttempts->get();
        $this->view->useCaptcha = $attempt && $attempt->surpassedMaxAllowed();
        $this->view->form = new Form_OpenidLogin(null, $this->view->base, $attempt && $attempt->surpassedMaxAllowed());

        if ($this->_getParam('invalidCaptcha')) {
            $this->view->form->captcha->addError($this->view->translate('Captcha value is wrong'));
        }

        if ($this->_getParam('invalidLogin')) {
            $this->view->form->addError($this->view->translate('Invalid credentials'));
        }

        if ($request->idSelect()) {
            $this->view->identity = false;
            $this->view->form->openIdIdentity->setRequired(true);
        } else {
            $this->view->identity = $request->identity;
        }

        $this->view->queryString = $this->_queryString();

        if ($this->user->role == Users_Model_User::ROLE_GUEST && @$_COOKIE['image']) {
            $images = new Users_Model_SigninImages();
            $this->view->image = $images->getByCookie($_COOKIE['image']);
        } else {
            $this->view->image = false;
        }
    }

    public function authenticateAction()
    {
        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();

        $authAttempts = new Users_Model_AuthAttempts();
        $attempt = $authAttempts->get();

        $form = new Form_OpenidLogin(null, $this->view->base, $attempt && $attempt->surpassedMaxAllowed());
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $formErrors = $form->getErrors();
            // gotta resort to pass errors as params because we don't use the session here
            if (@$formErrors['captcha']) {
                $this->_forward('login', null, null, array('invalidCaptcha' => true));
            } else {
                $this->_forward('login');
            }
            return;
        }

        $users = new Users_Model_Users();
        $result = $users->authenticate(
            $request->idSelect()? $form->getValue('openIdIdentity') : $request->identity,
            $this->_config->yubikey->enabled && $this->_config->yubikey->force?
                $form->getValue('yubikey')
                : $form->getValue('password'),
            true,
            $this->view
        );

        if ($result) {
            if ($attempt) {
                $attempt->delete();
            }
            $sites = new Model_Sites();
            $trustRoot = $this->_getTrustRoot($request);
            if ($sites->isTrusted($users->getUser(), $trustRoot)) {
                $this->_forward('proceed', null, null, array('allow' => true));
            } elseif ($sites->isNeverTrusted($users->getUser(), $trustRoot)) {
                $this->_forward('proceed', null, null, array('deny' => true));
            } else {
                $this->_forward('trust');
            }
        } else {
            if (!$attempt) {
                $authAttempts->create();
            } else {
                $attempt->addFailure();
                $attempt->save();
            }
            $this->_forward('login', null, null, array('invalidLogin' => true));
        }
    }

    public function trustAction()
    {
        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();

        $this->view->siteRoot = $this->_getTrustRoot($request);
        $this->view->identityUrl = $this->user->openid;
        $this->view->queryString = $this->_queryString();

        $this->view->showProfileForm = $this->_hasSreg($request);
    }

    public function proceedAction()
    {
        // needed for unit tests
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender(true);

        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();

        if ($request->idSelect()) {
            $id = $this->user->openid;
        } else {
            $id = null;
        }

        $response = $request->answer(true, null, $id);

        if ($this->_hasSreg($request)
                // profileId will be null if site is already trusted
                && $this->_getParam('profileId')) {
            $profiles = new Users_Model_Profiles();
            $profile = $profiles->getRowInstance($this->_getParam('profileId'));
            $personalInfoForm = Users_Form_PersonalInfo::getForm($request, $profile);
            $formData = $this->_request->getPost();
            $personalInfoForm->populate($formData);

            // not planning on validating stuff here yet, but I call this
            // for the date element to be filled properly
            $foo = $personalInfoForm->isValid($formData);

            $sregResponse = Auth_OpenID_SRegResponse::extractResponse(
                $personalInfoForm->getSregRequest(),
                $personalInfoForm->getUnqualifiedValues());
            $sregResponse->toMessage($response->fields);
        }

        $trustRoot= $this->_getTrustRoot($request);

        if ($this->_getParam('allow')) {
            if ($this->_getParam('forever')) {

                $sites = new Model_Sites();
                $sites->deleteForUserSite($this->user, $trustRoot);

                $siteObj = $sites->createRow();
                $siteObj->user_id = $this->user->id;
                $siteObj->site = $trustRoot;
                $siteObj->creation_date = date('Y-m-d');

                if (isset($personalInfoForm)) {
                    $trusted = array();
                    // using this key name for BC pre 1.1 when we used Zend_OpenId
                    $trusted['Zend_OpenId_Extension_Sreg'] = $personalInfoForm->getUnqualifiedValues();
                } else {
                    $trusted = true;
                }
                $siteObj->trusted = serialize($trusted);

                $siteObj->save();
            }

            $this->_saveHistory($trustRoot, Model_History::AUTHORIZED);

            require_once 'libs/Auth/OpenID/PAPE.php';
            if ($papeRequest = Auth_OpenID_PAPE_Request::fromOpenIDRequest($request)) {
                $this->_processPape($papeRequest, $response);
            }

            $webresponse = $server->encodeResponse($response);

            foreach ($webresponse->headers as $k => $v) {
                if ($k == 'location') {
                    $this->_response->setRedirect($v);
                } else {
                    $this->_response->setHeader($k, $v);
                }
            }

            $this->_response->setHeader('Connection', 'close');
            $this->_response->appendBody($webresponse->body);
        } elseif ($this->_getParam('deny')) {
            if ($this->_getParam('forever')) {
                $sites = new Model_Sites();
                $sites->deleteForUserSite($this->user, $trustRoot);

                $siteObj = $sites->createRow();
                $siteObj->user_id = $this->user->id;
                $siteObj->site = $trustRoot;
                $siteObj->creation_date = date('Y-m-d');
                $siteObj->trusted = serialize(false);
                $siteObj->save();
            }

            $this->_saveHistory($trustRoot, Model_History::DENIED);

            return $this->_sendResponse($server, $request->answer(false));
        }
    }

    private function _saveHistory($site, $result)
    {
        $histories = new Model_Histories();
        $history = $histories->createRow();
        $history->user_id = $this->user->id;
        $history->date = date('Y-m-d H:i:s');
        $history->site = $site;
        $history->ip = $_SERVER['REMOTE_ADDR'];
        $history->result = $result;
        $history->save();
    }

    private function _sendResponse(Auth_OpenID_Server $server, Auth_OpenID_ServerResponse $response)
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender(true);

        $webresponse = $server->encodeResponse($response);

        if ($webresponse->code != AUTH_OPENID_HTTP_OK) {
            $this->_response->setRawHeader(sprintf("HTTP/1.1 %d ", $webresponse->code), true, $webresponse->code);
        }

        foreach ($webresponse->headers as $k => $v) {
            if ($k == 'location') {
                $this->_response->setRedirect($v);
            } else {
                $this->_response->setHeader($k, $v);
            }
        }

        $this->_response->setHeader('Connection', 'close');

        $this->_response->appendBody($webresponse->body);
    }

    private function _getTrustRoot(Auth_OpenID_Request $request)
    {
        $trustRoot = $request->trust_root;
        Zend_OpenId::normalizeUrl($trustRoot);

        return $trustRoot;
    }

    private function _hasSreg(Auth_OpenID_Request $request)
    {
        // The class Auth_OpenID_SRegRequest is included in the following file
        require_once 'libs/Auth/OpenID/SReg.php';

        $sregRequest = Auth_OpenID_SRegRequest::fromOpenIDRequest($request);
        $props = $sregRequest->allRequestedFields();

        return (is_array($props) && count($props) > 0);
    }

    private function _processPape(Auth_OpenID_PAPE_Request $papeRequest, $response)
    {
        if (($image = $this->user->getImage()) && @$_COOKIE['image']) {
            $cidSupportedPolicies = array(PAPE_AUTH_PHISHING_RESISTANT);
            if ($RPPreferredTypes = $papeRequest->preferredTypes($cidSupportedPolicies)) {
                $this->user->getLastLoginUtc();
                $papeResponse = new Auth_OpenID_PAPE_Response(
                    $cidSupportedPolicies,
                    $this->user->getLastLoginUtc()
                );
                $papeResponse->toMessage($response->fields);
            }
        }
    }
}
