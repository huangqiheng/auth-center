<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class CidController extends CommunityID_Controller_Action
{
    const NEWS_CONTENT_MAX_LENGTH = 100;
    const NEWS_NUM_ITEMS = 6;

    protected $_numCols = 2;

    public function indexAction()
    {
        $this->view->version = Application::VERSION;

        try {
            $feed = Zend_Feed::import('http://source.keyboard-monkeys.org/projects/communityid/news?format=atom');
        } catch (Zend_Exception $e) {
            // feed import failed
            $obj = new StdClass();
            $obj->link = array('href' => '');
            $obj->title = $this->view->translate('Could not retrieve news items');
            $obj->updated = '';
            $obj->content = '';
            $feed = array($obj);
        }

        $this->view->news = array();
        $i = 0;
        foreach ($feed as $item) {
            if ($i++ >= self::NEWS_NUM_ITEMS) {
                break;
            }

            // ATOM uses <link href="foo" />, while RSS uses <link>foo</link>
            $item->link = $item->link['href']? $item->link['href'] : $item->link;

            if (strlen($item->content) > self::NEWS_CONTENT_MAX_LENGTH) {
                $item->content = substr($item->content, 0, self::NEWS_CONTENT_MAX_LENGTH)
                               . '...<br /><a class="readMore" href="'.$item->link.'">' . $this->view->translate('Read More') . '</a>';
            }
            $this->view->news[] = $item;
        }

        $this->_helper->actionStack('index', 'login', 'users');
    }
}
