<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class News_IndexController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        $news = new News_Model_News();

        $this->view->paginator = $news->getArticlesPaginator(News_Model_News::RECORDS_PER_PAGE,
            $this->_getParam('page', 0), $this->user);

        $this->_helper->actionStack('index', 'login', 'users');
    }
}
