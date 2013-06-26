<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Model_AuthAttempts extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'auth_attempts';
    protected $_primary = 'id';
    protected $_rowClass = 'Users_Model_AuthAttempt';

    /**
    * This method first searches for a match on the session_id.
    * If nothing is found, it searches for a match on the IP.
    */
    public function get()
    {
        $ip = @$_SERVER['REMOTE_ADDR'];

        $select = $this->select()
                       ->where('session_id=?', session_id());

        $row = $this->fetchRow($select);
        if ($row) {
            return $row;
        }

        $select = $select->where('IP=?', $ip);

        return $this->fetchRow($select);
    }
    
    public function create()
    {
        $ip = @$_SERVER['REMOTE_ADDR'];

        $attempt = $this->createRow();
        $attempt->IP = $ip;
        $attempt->session_id = session_id();
        $attempt->failed_attempts = 1;
        $attempt->last_attempt = date('Y-m-d H:i:s');
        $attempt->save();
    }
}
