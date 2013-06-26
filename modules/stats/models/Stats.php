<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Stats_Model_Stats
{
    private $_db;

    static public $weekDays = array('S', 'M', 'T', 'W', 'T', 'F', 'S');
    static public $months = array(1 => 'J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');

    public function __construct()
    {
        $this->_db = Zend_Registry::get('db');
    }

    /**
    * @return Array
    */
    public function getNumRegisteredUsersDays($unixDateStart, $unixDateEnd, $countUnconfirmed = false)
    {
        $select = $this->_db->select()->from('users', array('registration_date' => 'registration_date', 'users' => 'COUNT(registration_date)'))
                                      ->where('registration_date >= ?', strftime('%Y-%m-%d', $unixDateStart))
                                      ->where('registration_date < ?', strftime('%Y-%m-%d', $unixDateEnd))
                                      ->group('registration_date')
                                      ->order('registration_date');

        if (!$countUnconfirmed) {
            $select = $select->where('users.role != ?', Users_Model_User::ROLE_GUEST);
        }

        return $this->_db->fetchAssoc($select);
    }

    /**
    * @return Array
    */
    public function getNumRegisteredUsersYear($unixDateStart, $unixDateEnd, $countUnconfirmed = false)
    {
        $select = $this->_db->select()->from('users', array('registration_date' => 'MONTH(registration_date)', 'users' => 'COUNT(MONTH(registration_date))'))
                                      ->where('registration_date >= ?', strftime('%Y-%m-%d', $unixDateStart))
                                      ->where('registration_date <= ?', strftime('%Y-%m-%d', $unixDateEnd))
                                      ->group('MONTH(registration_date)')
                                      ->order('registration_date');

        if (!$countUnconfirmed) {
            $select = $select->where('users.role != ?', Users_Model_User::ROLE_GUEST);
        }

        return $this->_db->fetchAssoc($select);
    }

    /**
    * @return int
    */
    public function getNumRegisteredUsers($unixDate, $countUnconfirmed = false)
    {
        $select = $this->_db->select()->from('users')
                                      ->where('registration_date < ?', strftime('%Y-%m-%d', $unixDate));

        if (!$countUnconfirmed) {
            $select = $select->where('users.role != ?', Users_Model_User::ROLE_GUEST);
        }


        $statement = $this->_db->prepare($select);
        $statement->execute();

        return $statement->rowCount();
    }

    /**
    * @return Array
    */
    public function getAllTestUsersIds()
    {
        $select = $this->_db->select()->from('users', 'id');
        
        return $this->_db->fetchAll($select);
    }

    /**
    * @return Array
    */
    public function getNumAuthorizationsDays($unixDateStart, $unixDateEnd)
    {
        $select = $this->_db->select()->from('history', array('date' => 'DATE(date)', 'entry' => 'COUNT(DATE(date))'))
                                      ->where('date>= ?', strftime('%Y-%m-%d', $unixDateStart))
                                      ->where('date< ?', strftime('%Y-%m-%d', $unixDateEnd))
                                      ->group('DATE(date)')
                                      ->order('date');

        return $this->_db->fetchAssoc($select);
    }

    /**
    * @return Array
    */
    public function getNumAuthorizationsYear($unixDateStart, $unixDateEnd)
    {
        $select = $this->_db->select()->from('history', array('date' => 'MONTH(date)', 'entry' => 'COUNT(MONTH(date))'))
                                      ->where('date>= ?', strftime('%Y-%m-%d', $unixDateStart))
                                      ->where('date<= ?', strftime('%Y-%m-%d', $unixDateEnd))
                                      ->group('MONTH(date)')
                                      ->order('date');

        return $this->_db->fetchAssoc($select);
    }

    /**
    * @return int
    */
    public function getNumTrustedSites($unixDate)
    {
        $select = $this->_db->select()->from('sites')
                                      ->where('creation_date < ?', strftime('%Y-%m-%d', $unixDate));


        $statement = $this->_db->prepare($select);
        $statement->execute();

        return $statement->rowCount();
    }

    /**
    * @return Array
    */
    public function getNumTrustedSitesDays($unixDateStart, $unixDateEnd)
    {
        $select = $this->_db->select()->from('sites', array('creation_date' => 'creation_date', 'site' => 'COUNT(creation_date)'))
                                      ->where('creation_date>= ?', strftime('%Y-%m-%d', $unixDateStart))
                                      ->where('creation_date< ?', strftime('%Y-%m-%d', $unixDateEnd))
                                      ->group('creation_date')
                                      ->order('creation_date');

        return $this->_db->fetchAssoc($select);
    }

    /**
    * @return Array
    */
    public function getNumTrustedSitesYear($unixDateStart, $unixDateEnd)
    {
        $select = $this->_db->select()->from('sites', array('creation_date' => 'MONTH(creation_date)', 'site' => 'COUNT(MONTH(creation_date))'))
                                      ->where('creation_date>= ?', strftime('%Y-%m-%d', $unixDateStart))
                                      ->where('creation_date<= ?', strftime('%Y-%m-%d', $unixDateEnd))
                                      ->group('MONTH(creation_date)')
                                      ->order('creation_date');

        return $this->_db->fetchAssoc($select);
    }

    /**
    * @return Array
    */
    public function getTopTenSites()
    {
        $select = $this->_db->select()->from('sites', array('num' => 'COUNT(site)', 'site' => 'site'))
                                      ->group('site')
                                      ->order('num DESC')
                                      ->limit(10);

        return $this->_db->fetchAll($select);
    }
}
