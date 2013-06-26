<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

abstract class CommunityID_UpgradeStage
{
    protected $_view;
    protected $_user;
    protected $_db;

    public function __construct(Users_Model_User $user, Zend_Db_Adapter_Abstract $db, Zend_View $view)
    {
        $this->_user = $user;
        $this->_view = $view;
        $this->_db = $db;
    }

    public function requirements()
    {
        return array();
    }

    abstract function proceed();
}
