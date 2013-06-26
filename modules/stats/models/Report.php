<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

abstract class Stats_Model_Report
{
    const STATS_PLUGIN_DIR = '/plugins/stats';

    protected $_controllerAction;
    protected $view;

    public abstract function getTitle();

    public abstract function getPriority();

    public abstract function setTemplateVars();

    public function renderGraph() {}

    public function setView(Zend_View $view)
    {
        $this->view = $view;
    }

    public function setControllerAction(CommunityID_Controller_Action $controllerAction)
    {
        $this->_controllerAction = $controllerAction;
    }

    public function getIdentifier()
    {
        return md5($this->getTitle());
    }

    public function getClassName()
    {
        return get_class($this);
    }

    public static function getReportInstance($reportName)
    {
        $statPath = APP_DIR . self::STATS_PLUGIN_DIR . "/$reportName.php";
        if (Zend_Registry::get('config')->environment->production) {
            $includeResult = @include $statPath;
        } else {
            $includeResult = include $statPath;
        }
        if (!$includeResult) {
            throw new Monkeys_AccessDeniedException();
            Zend_Registry::get('logger')->log("Unable to open Stats plugin: $statPath", Zend_Log::WARN);
            continue;
        }

        $statPlugin = new $reportName();

        return $statPlugin;
    }
}
