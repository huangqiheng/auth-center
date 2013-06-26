<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Stats_IndexController extends CommunityID_Controller_Action
{
    const LOCATION_LEFT = 0;
    const LOCATION_RIGHT = 1;

    protected $_numCols = 1;

    public function indexAction()
    {
        $statPlugins = array();
        $this->view->pluginsLeft = array();
        $this->view->pluginsRight = array();

        $dir = dir(APP_DIR . Stats_Model_Report::STATS_PLUGIN_DIR);
        $i = 0;
        while (false !== ($entry = $dir->read())) {
            if (in_array($entry, array('.', '..'))
                    || substr($entry, -4) != '.php') {
                continue;
            }

            try {
                $reportName = substr($entry, 0, -4);
                $statPlugins[$i] = Stats_Model_Report::getReportInstance($reportName);
                $statPlugins[$i]->setView($this->view);
            } catch (Monkeys_AccessDeniedException $ex) {
                Zend_Registry::get('logger')->log("Unable to open Stats plugin: $entry", Zend_Log::WARN);
                continue;
            }
            $i++;
        }
        $dir->close();
        usort($statPlugins, array($this, '_sortPlugins'));

        $location = self::LOCATION_LEFT;
        foreach ($statPlugins as $statPlugin) {
            if ($location == self::LOCATION_LEFT) {
                $this->view->pluginsLeft[] = $statPlugin;
                $location = self::LOCATION_RIGHT;
            } else {
                $this->view->pluginsRight[] = $statPlugin;
                $location = self::LOCATION_LEFT;
            }
        }
    }

    private function _sortPlugins(Stats_Model_Report $pluginA, Stats_Model_Report $pluginB)
    {
        return $pluginA->getPriority() - $pluginB->getPriority();
    }
}
