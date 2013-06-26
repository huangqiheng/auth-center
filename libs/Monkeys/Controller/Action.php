<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

abstract class Monkeys_Controller_Action extends Zend_Controller_Action
{
    /**
    * not prepended with "_" because their view counterparts can't have "_" prepended
    */
    protected $user;
    protected $targetUser;

    protected $_config;
    protected $_settings;
    protected $_numCols = 2;
    protected $_title = '';
    protected $underMaintenance = false;

    public function init()
    {
        Zend_Registry::get('logger')->log('Route used: ' . Application::$front->getRouter()->getCurrentRouteName(), Zend_Log::DEBUG);
        $this->_config = Zend_Registry::get('config');
        $this->_settings = new Model_Settings();

        if ($this->_request->getModuleName() != 'install'
                && strtoupper(get_class($this)) != 'ERRORCONTROLLER'
                && $this->_needsUpgrade()) {
            $this->_redirect('/install/upgrade');
            return;
        }

        if (!Zend_Registry::isRegistered('user')) {
            // guest user
            $users = new Users_Model_Users();
            $user = $users->createRow();
            Zend_Registry::set('user', $user);
        }

        $this->user = Zend_Registry::get('user');
        $this->view->user = $this->user;

        $this->_validateTargetUser();
        $this->_checkMaintenanceMode();

        $this->view->controller = $this;

        $this->view->addHelperPath('libs/Monkeys/View/Helper', 'Monkeys_View_Helper');
        $this->view->setUseStreamWrapper(true);
        $this->_addCustomTemplatePath();
        $this->view->addBasePath(APP_DIR . '/views');
        $this->_addCustomTemplatePath();
        $this->_setBase();
        $this->view->numCols = $this->_numCols;

        $this->view->module = $this->getRequest()->getModuleName();

        if ($this->_getParam('subtitle')) {
            $this->view->pageSubtitle = $this->view->escape($this->_getParam('subtitle'));
        }

        if ($this->getRequest()->getParam('next')) {
            $this->view->nextAction = $this->getRequest()->getParam('next');
        } else {
            $this->view->nextAction = '';
        }

        $this->view->messages = $this->_helper->FlashMessenger->getMessages();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $slowdown = $this->_config->environment->ajax_slowdown;
            if ($slowdown > 0) {
                sleep($slowdown);
            }
            $this->_helper->layout->disableLayout();
        } else {
            $this->view->version = Application::VERSION;
            $this->view->loaderCombine = $this->_config->environment->YDN? 'true' : 'false';
            $this->view->loaderBase = $this->_config->environment->YDN?
                                        'http://yui.yahooapis.com/2.7.0/build/'
                                        : $this->view->base . '/javascript/yui/';
        }

        $this->view->min = $this->_config->environment->production ? '-min' : '';
    }

    public function postDispatch()
    {
        $this->view->title = $this->_title;
    }

    private function _addCustomTemplatePath()
    {
        if (($template = $this->_config->environment->template) == 'default') {
            return;
        }

        $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        $view = $viewRenderer->view;
        $scriptPaths = $view->getScriptPaths();
        $oldPath = $scriptPaths[0];
        $newPath = substr($oldPath, 0, strrpos($oldPath, DIRECTORY_SEPARATOR, -2) + 1) . "scripts_$template" . DIRECTORY_SEPARATOR;
        $view->addScriptPath($newPath);
    }

    protected function _setBase()
    {
        $this->view->base = $this->view->getBase();
    }

    protected abstract function _validateTargetUser();

    protected function _needsUpgrade()
    {
        require 'setup/versions.php';

        $lastVersion = array_pop($versions);

        return $lastVersion != $this->_getDbVersion();
    }

    protected function _getDbVersion()
    {
        if (!$version = $this->_settings->getVersion()) {
            $version = '1.0.1';
        }

        return $version;
    }

    protected function _checkMaintenanceMode()
    {
        if (!$this->_config->environment->installed) {
            $this->underMaintenance = true;
            $this->view->underMaintenance = false;
            return;
        }

        $this->underMaintenance = $this->_settings->isMaintenanceMode();
        $this->view->underMaintenance = $this->underMaintenance;
    }

    protected function _redirectForMaintenance($backToNormalConnection = false)
    {
        if ($backToNormalConnection) {
            $this->_redirectToNormalConnection('');
        } else {
            $this->_redirect('');
        }
    }

    protected function _redirect($url, $options = array())
    {
        Zend_Registry::get('logger')->log("redirected to '$url'", Zend_Log::DEBUG);

        return parent::_redirect($url, $options);
    }

    public static function getProtocol()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            return 'https';
        } else {
            return 'http';
        }
    }

    protected function _checkPermission($permission)
    {
        if (!$this->_hasPermission($permission)) {
            throw new Monkeys_AccessDeniedException();
        }
    }

    protected function _overrideNumCols($numCols)
    {
        $this->view->numCols = $this->_numCols = $numCols;
    }
}
