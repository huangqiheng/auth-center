<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class SitesController extends CommunityID_Controller_Action
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

        $fields = new Model_Fields();
        $sites = new Model_Sites();
        $sitesRows = $sites->get(
            $this->user,
            $this->_getParam('startIndex'),
            $this->_getParam('results')
        );

        $jsonObj = new StdClass();
        $jsonObj->recordsReturned = count($sitesRows);
        $jsonObj->totalRecords = $sites->getNumSites($this->user);
        $jsonObj->startIndex = $this->_getParam('startIndex');
        $jsonObj->sort = null;
        $jsonObj->dir = 'asc';
        $jsonObj->records = array();

        foreach ($sitesRows as $site) {
            $jsonObjSite = new StdClass();
            $jsonObjSite->id = $site->id;
            $jsonObjSite->site = $site->site;

            $trusted = unserialize($site->trusted);
            $jsonObjSite->trusted = (is_bool($trusted) && $trusted) || is_array($trusted);

            if (is_array($trusted)
                && isset($trusted['Zend_OpenId_Extension_Sreg'])
                && count($trusted['Zend_OpenId_Extension_Sreg']) > 0)
            {
                $translatedTrusted = array();
                foreach ($trusted['Zend_OpenId_Extension_Sreg'] as $identifier => $value) {
                    $translatedTrusted[$this->view->translate($fields->getFieldName($identifier))] = $value;
                }
                $jsonObjSite->infoExchanged = $translatedTrusted;
            } else {
                $jsonObjSite->infoExchanged = false;
            }

            $jsonObj->records[] = $jsonObjSite;
        }

        echo Zend_Json::encode($jsonObj);
    }

    public function denyAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);

        $sites = new Model_Sites();
        $site = $sites->getRowInstance($this->_getParam('id'));
        if ($site->user_id != $this->user->id) {
            throw new Monkeys_AccessDeniedException();
        }

        $site->trusted = serialize(false);
        $site->save();

        $json = new StdClass();
        $json->code  = 200;

        echo Zend_Json::encode($json);
    }

    public function allowAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);

        $sites = new Model_Sites();
        $site = $sites->getRowInstance($this->_getParam('id'));
        if ($site->user_id != $this->user->id) {
            throw new Monkeys_AccessDeniedException();
        }

        $site->trusted = serialize(true);
        $site->save();

        $json = new StdClass();
        $json->code  = 200;

        echo Zend_Json::encode($json);
    }

    public function deleteAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);

        $sites = new Model_Sites();
        $site = $sites->getRowInstance($this->_getParam('id'));
        if ($site->user_id != $this->user->id) {
            throw new Monkeys_AccessDeniedException();
        }

        $site->delete();

        $json = new StdClass();
        $json->code  = 200;

        echo Zend_Json::encode($json);
    }
}
