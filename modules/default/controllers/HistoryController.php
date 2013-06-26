<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class HistoryController extends CommunityID_Controller_Action
{
    public function preDispatch()
    {
        if ($this->user->role == Users_Model_User::ROLE_ADMIN) {
            throw new Monkeys_AccessDeniedException();
        }
    }

    public function indexAction()
    {
        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function listAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);

        $histories = new Model_Histories();
        $historiesRows = $histories->get(
            $this->user,
            $this->_getParam('startIndex'),
            $this->_getParam('results'),
            $this->_getParam('sort', 'date'),
            $this->_getParam('dir', Model_Histories::DIR_DESC)
        );

        $jsonObj = new StdClass();
        $jsonObj->recordsReturned = count($historiesRows);
        $jsonObj->totalRecords = $histories->getNumHistories($this->user);
        $jsonObj->startIndex = $this->_getParam('startIndex');
        $jsonObj->sort = null;
        $jsonObj->dir = 'asc';
        $jsonObj->records = array();

        foreach ($historiesRows as $history) {
            $jsonObjSite = new StdClass();
            $jsonObjSite->id = $history->id;
            $jsonObjSite->date = $history->date;
            $jsonObjSite->site = $history->site;
            $jsonObjSite->ip = $history->ip;
            $jsonObjSite->result = $history->result;

            $jsonObj->records[] = $jsonObjSite;
        }

        echo Zend_Json::encode($jsonObj);
    }

    public function clearAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);

        $histories = new Model_Histories();
        $histories->clear($this->user);

        $json = new StdClass();
        $json->code  = 200;

        echo Zend_Json::encode($json);
    }
}
