<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    private $_acl;

    public function __construct($acl)
    {
        $this->_acl = $acl;
    }

    /**
    * Here we only check for the basic action access permissions.
    * In Monkeys_Controller_Action we check for more specific permissions
    */
    public function preDispatch($request)
    {
        if (!Zend_Registry::get('config')->environment->installed
            && $request->getModuleName() != 'install'
            && $request->getControllerName() != 'error')
        {
            $request->setModuleName('install');
            $request->setControllerName('index');
            $request->setActionName('index');

            return;
        }

        if (Zend_Registry::isRegistered('user')) {
            // used by unit tests to inject the logged-in user
            $user= Zend_Registry::get('user');
        } else {
            $auth = Zend_Auth::getInstance();
            $users = new Users_Model_Users();
            if ($auth->hasIdentity()) {
                $user = $auth->getStorage()->read();
                $user->init();

                // reactivate row as live data
                $user->setTable($users);
            } else {
                // guest user
                $user = $users->createRow();
            }

            Zend_Registry::set('user', $user);
        }

        $resource   = $request->getModuleName() . '_' . $request->getControllerName();

        if (!$this->_acl->has($resource)) {
            //echo "role: " . $user->role . " - resource: $resource - privilege: " . $request->getActionName() . "<br>\n";exit;
            throw new Monkeys_BadUrlException($this->getRequest()->getRequestUri());
        }

        // if an admin is not allowed for this action, then the action doesn't exist
        if (!$this->_acl->isAllowed(Users_Model_User::ROLE_ADMIN, $resource, $request->getActionName())) {
            //echo "role: " . $user->role . " - resource: $resource - privilege: " . $request->getActionName() . "<br>\n";exit;
            throw new Monkeys_BadUrlException($this->getRequest()->getRequestUri());
        }

        if (!$this->_acl->isAllowed($user->role, $resource, $request->getActionName())) {
            //echo "role: " . $user->role . " - resource: $resource - privilege: " . $request->getActionName() . "<br>\n";exit;
            throw new Monkeys_AccessDeniedException();
        }
    }
}
