<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Model_Histories extends Monkeys_Db_Table_Gateway
{
    const DIR_ASC = 0;
    const DIR_DESC = 1;

    private $_sortFields = array(
        'date'          => array('date', 'site', 'ip', 'result'),
        'site'          => array('site', 'date', 'ip', 'result'),
        'ip'            => array('ip', 'date', 'site', 'result'),
        'result'        => array('result', 'date', 'site', 'ip'),
    );

    protected $_name = 'history';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_History';

    public function get(Users_Model_User $user, $startIndex = false, $results = false, $sort = false, $dir = false)
    {
        $select = $this->select()
                       ->where('user_id=?', $user->id);

        if ($sort && isset($this->_sortFields[$sort])) {
            $dir = ($dir == self::DIR_ASC? 'ASC' : 'DESC');
            $sortSql = array();
            foreach ($this->_sortFields[$sort] as $field) {
                $sortSql[] = "$field $dir";
            }

            $select = $select->order($sortSql);
        }

        if ($startIndex !== false && $results !== false) {
            $select = $select->limit($results, $startIndex);
        }

        return $this->fetchAll($select);
    }

    public function getNumHistories(Users_Model_User $user)
    {
        $sites = $this->get($user);

        return count($sites);
    }

    public function clear(Users_Model_User $user)
    {
        $where = $this->getAdapter()->quoteInto('user_id=?', $user->id);
        $this->delete($where);
    }

    public function clearOldEntries()
    {
        $days = Zend_Registry::get('config')->environment->keep_history_days;

        $where = $this->getAdapter()->quoteInto('date < ?', date('Y-m-d', time() - $days * 86400));
        $this->delete($where);
    }
}
