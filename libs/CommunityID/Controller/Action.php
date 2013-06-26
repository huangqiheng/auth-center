<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

abstract class CommunityID_Controller_Action extends Monkeys_Controller_Action
{
    /**
    * flag to avoid duplicating the metas by the various controllers
    * that can make a single page
    */
    private static $_metasRendered = false;

    public function init()
    {
        parent::init();

        if (!self::$_metasRendered) {
            if (@$this->_config->metadata->description) {
                $this->view->headMeta()->appendName('description', $this->_config->metadata->description);
            }
            if (@$this->_config->metadata->keywords) {
                $this->view->headMeta()->appendName('keywords', $this->_config->metadata->keywords);
            }
            self::$_metasRendered = true;
        }

        Zend_Controller_Action_HelperBroker::addPrefix('CommunityID_Controller_Action_Helper');
    }

    protected function _setBase()
    {
        if ($this->_config->subdomain->enabled) {
            $protocol = self::getProtocol();

            $this->view->base = "$protocol://"
                                   . ($this->_config->subdomain->use_www? 'www.' : '')
                                   . $this->_config->subdomain->hostname;
        } else {
            $this->view->base = $this->view->getBase();
        }
    }

    protected function _validateTargetUser()
    {
        if (Zend_Registry::isRegistered('targetUser')) {
            // used by unit tests to inject the target user
            $this->targetUser = Zend_Registry::get('targetUser');
        } else {
            $userId = $this->_getParam('userid');

            if (is_null($userId)) {
                $this->targetUser = $this->user;
            } elseif ($this->_getParam('userid') == 0) {
                $users = new Users_Model_Users();
                $this->targetUser = $users->createRow();
            } else {
                if ($userId != $this->user->id && $this->user->role != Users_Model_User::ROLE_ADMIN) {
                    $this->_helper->FlashMessenger->addMessage($this->view->translate('Error: Invalid user id'));
                    $this->_redirect('profile/edit');
                }

                $users = new Users_Model_Users();
                $this->targetUser = $users->getRowInstance($userId);

                if ($this->_config->ldap->enabled) {
                    $ldap = Monkeys_Ldap::getInstance();
                    $ldapUserData = $ldap->get("cn={$this->targetUser->username},{$this->_config->ldap->baseDn}");
                    $this->targetUser->overrideWithLdapData($ldapUserData, true);
                }
            }
        }

        $this->view->targetUser = $this->targetUser;
    }

    protected function _redirectToNormalConnection()
    {
        if ($this->_config->SSL->enable_mixed_mode) {
            if ($this->_config->subdomain->enabled) {
                // in this case $this->view->base contains the full URL, so we just gotta replace the protocol
                $this->_redirect('http' . substr($this->view->base, strpos($this->view->base, '://')));
            } else {
                $this->_redirect('http://' . $_SERVER['HTTP_HOST'] . $this->view->base);
            }
        } else {
            $this->_redirect('');
        }
    }

    /**
    * Circumvent PHP's automatic replacement of dots by underscore in var names in $_GET and $_POST
    */
    protected function _queryString()
    {
        $unfilteredVars = array_merge($_GET, $_POST);
        $varsTemp = array();
        $vars = array();
        $extensions = array();
        foreach ($unfilteredVars as $key => $value) {
            if ($key == 'password') {
                continue;
            }

            if (substr($key, 0, 10) == 'openid_ns_') {
                $extensions[] = substr($key, 10);
                $varsTemp[str_replace('openid_ns_', 'openid.ns.', $key)] = $value;
            } else {
                $varsTemp[str_replace('openid_', 'openid.', $key)] = $value;
            }
        }
        foreach ($extensions as $extension) {
            foreach ($varsTemp as $key => $value) {
                if (strpos($key, "openid.$extension") === 0) {
                    $prefix = "openid.$extension.";
                    $key = $prefix . substr($key, strlen($prefix));
                }
                $vars[$key] = $value;
            }
        }
        if (!$extensions) {
            $vars = $varsTemp;
        }

        return '?' . http_build_query($vars);
    }

    protected function _getOpenIdProvider()
    {
        $connection = new CommunityID_OpenId_DatabaseConnection(Zend_Registry::get('db'));
        $store = new Auth_OpenID_MySQLStore($connection, 'associations', 'nonces');
        $server = new Auth_OpenID_Server($store, $this->_helper->ProviderUrl($this->_config));

        return $server;
    }
}
