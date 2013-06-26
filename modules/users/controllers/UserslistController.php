<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_UserslistController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);

        $users = new Users_Model_Users();

        switch($this->_getParam('filter')) {
            case 'confirmed':
                $where = "accepted_eula=1 AND role != '".Users_Model_User::ROLE_ADMIN."'";
                break;
            case 'unconfirmed':
                $where = "accepted_eula=0 AND role != '".Users_Model_User::ROLE_ADMIN."'";
                break;
            default:
                $where = false;
                break;
        }

        // This retrieves user data from the users table, even if using LDAP. This means the user's full name
        // might be out of sync with what it's in LDAP. No biggie since user's names rarely change ;)
        // However do know that a given user name is synced with LDAP every time he logs in.
        $usersRows = $users->getUsers(
            $this->_getParam('startIndex'),
            $this->_getParam('results'),
            $this->_getParam('sort', 'registration'),
            $this->_getParam('dir', Users_Model_Users::DIR_DESC),
            $where,
            trim($this->_getParam('search')));

        $jsonObj = new StdClass();
        $jsonObj->recordsReturned = count($usersRows);
        $jsonObj->totalRecords = $users->getNumUsers($where, trim($this->_getParam('search')));
        $jsonObj->totalUsers = $users->getNumUsers();
        $jsonObj->totalUnconfirmedUsers = $users->getNumUnconfirmedUsers();
        $jsonObj->startIndex = $this->_getParam('startIndex');
        $jsonObj->sort = $this->_getParam('sort');
        $jsonObj->dir = $this->_getParam('dir');
        $jsonObj->records = array();

        foreach ($usersRows as $user) {
            if ($user->role == Users_Model_User::ROLE_ADMIN) {
                if ($this->_config->ldap->enabled && $user->username != $this->_config->ldap->admin) {
                    // this is the admin created during the installation, that is not used when ldap is enabled
                    continue;
                }
                $status = $this->view->translate('admin');
            } else if ($user->accepted_eula) {
                $status = $this->view->translate('confirmed');
            } else {
                $status = $this->view->translate('unconfirmed');
            }
            $jsonObjUser = new StdClass();
            $jsonObjUser->id = $user->id;
            $jsonObjUser->name = $user->getFullName();
            $jsonObjUser->registration = $user->registration_date;
            $jsonObjUser->role = $user->role;
            $jsonObjUser->status = $status;
            $jsonObjUser->reminders = $user->accepted_eula? 0 : $user->reminders;
            $jsonObj->records[] = $jsonObjUser;
        }

        echo Zend_Json::encode($jsonObj);
    }
}
