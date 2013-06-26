<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Model_Sites extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'sites';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_Site';

    private $_userSites = array();

    public function deleteForUserSite(Users_Model_User $user, $site)
    {
        $where1 = $this->getAdapter()->quoteInto('user_id=?',$user->id);
        $where2 = $this->getAdapter()->quoteInto('site=?', $site);
        $this->delete("$where1 AND $where2");
    }

    public function getSites(Users_Model_User $user)
    {
        if (!isset($this->_userSites[$user->username])) {
            $select = $this->select()
                           ->where('user_id=?', $user->id);

            $this->_userSites[$user->username] = $this->fetchAll($select);
        }

        return $this->_userSites[$user->username];
    }

    public function get(Users_Model_User $user, $startIndex, $results)
    {
        $select = $this->select()
                       ->where('user_id=?', $user->id);

        if ($startIndex !== false && $results !== false) {
            $select = $select->limit($results, $startIndex);
        }

        return $this->fetchAll($select);
    }

    public function getNumSites(Users_Model_User $user)
    {
        $sites = $this->get($user, false, false);

        return count($sites);
    }

    public function isTrusted(Users_Model_User $user, $site)
    {
        foreach ($this->getSites($user) as $userSite) {
            if ($userSite->site == $site && $userSite->trusted != 'b:0;') {
                return true;
            }
        }

        return false;
    }

    public function isNeverTrusted(Users_Model_User $user, $site)
    {
        foreach ($this->getSites($user) as $userSite) {
            if ($userSite->site == $site && $userSite->trusted == 'b:0;') {
                return true;
            }
        }

        return false;
    }
}
