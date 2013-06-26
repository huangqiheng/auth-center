<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_ProfileController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        if (!$this->targetUser->id && $this->user->role != Users_Model_User::ROLE_ADMIN) {
            throw new Monkeys_AccessDeniedException();
        }

        $this->view->canEditAccountInfo = !$this->_config->ldap->enabled
            || ($this->_config->ldap->enabled && $this->_config->ldap->keepRecordsSynced);
        $this->view->canChangePassword = !$this->_config->ldap->enabled
            || ($this->_config->ldap->enabled && $this->_config->ldap->canChangePassword);

        $this->view->yubikey = $this->_config->yubikey;

        $this->_helper->actionStack('index', 'login', 'users');
    }
}
