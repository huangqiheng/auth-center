<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class News_Model_News extends Monkeys_Db_Table_Gateway
{
    const RECORDS_PER_PAGE = 5;

    protected $_name = 'news';
    protected $_primary = 'id';
    protected $_rowClass = 'News_Model_NewsArticle';

    private $_sortFields = array(
        'date'          => array('date', 'title'),
        'title'         => array('title', 'date')
    );

    public function getArticlesPaginator($limit = self::RECORDS_PER_PAGE, $page = 0, Users_Model_User $user)
    {
        $select = $this->select()->order('date DESC');

        if ($user->role != Users_Model_User::ROLE_ADMIN) {
            $select = $select->where('date <= ?', date('Y-m-d H:i:s'));
        }

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbTableSelect($select));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);

        return $paginator;
    }

    public function getLatest($numItems, Users_Model_User $user)
    {
        $select = $this->select()
                       ->order('date DESC')
                       ->limit($numItems);

        if ($user->role != Users_Model_User::ROLE_ADMIN) {
            $select = $select->where('date <= ?', date('Y-m-d H:i:s'));
        }
        
        return $this->fetchAll($select);
    }

    public function deleteTestEntries()
    {
        $this->delete('test=1');
    }
}
