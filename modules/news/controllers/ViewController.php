<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class News_ViewController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        $news = new News_Model_News();
        $this->view->article = $news->getRowInstance($this->_getParam('id'));

        if ($this->view->article->date > date('Y-m-d H:i:s') && $this->user->role != Users_Model_User::ROLE_ADMIN) {
            throw new Monkeys_AccessDeniedException();
        }

        $this->_helper->actionStack('index', 'login', 'users');
    }
}
