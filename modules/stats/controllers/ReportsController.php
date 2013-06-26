<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Stats_ReportsController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        $statPlugin = $this->_getPlugin();
        $statPlugin->setTemplateVars();

        $pluginView = clone $this->view;
        $pluginView->plugin = $statPlugin;
        $pluginView->setScriptPath(APP_DIR . Stats_Model_Report::STATS_PLUGIN_DIR);
        $this->view->reportTitle = $statPlugin->getTitle();
        $this->view->content = $pluginView->render($statPlugin->getClassName().'.phtml');
    }

    public function graphAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);
        $this->_helper->layout->disableLayout();
        $statPlugin = $this->_getPlugin();
        $statPlugin->renderGraph();
    }

    private function _getPlugin()
    {
        $reportName = $this->_getParam('report');

        try {
            $statPlugin = Stats_Model_Report::getReportInstance($reportName);
        } catch (Monkeys_AccessDeniedException $ex) {
            throw new Exception("Unable to open Stats plugin: $entry");
        }

        $statPlugin->setControllerAction($this);
        $statPlugin->setView($this->view);

        return $statPlugin;
    }
}

