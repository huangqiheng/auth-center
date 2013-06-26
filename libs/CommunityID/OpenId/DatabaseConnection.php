<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class CommunityId_OpenId_DatabaseConnection extends Auth_OpenID_DatabaseConnection
{
    private $_db;

    public function __construct(Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
    }

    public function autoCommit($mode)
    {
        // we'll use autocommit
        return true;
    }

    public function begin()
    {
        // unsupported
    }

    public function commit()
    {
        // unsupported
    }

    public function getAll($sql, $params = array())
    {
        $query = $this->_db->query($sql, $params);

        return $result->fetchAll();
    }
    
    public function getOne($sql, $params = array())
    {
        $query = $this->_db->query($sql, $params);

        $result = $query->fetch();
        if (!$result) {
            return false;
        }

        return $result[0];
    }

    public function getRow($sql, $params = array())
    {
        $query = $this->_db->query($sql, $params);

        return $query->fetch();
    }

    public function query($sql, $params = array())
    {
        $this->_db->query($sql, $params);
    }

    public function rollback()
    {
        // unsupported
    }

    /**
    * Not part of the interface, but bug in Auth_OpenID_SQLStore obliges me to define it
    */
    public function setFetchMode($whateva)
    {
    }
}
