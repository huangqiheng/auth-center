<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Top extends Stats_Model_Report
{
    public function getPriority()
    {
        return 4;
    }

    public function getTitle()
    {
        return $this->view->translate('Top 10 Trusted Sites');
    }

    public function setTemplateVars()
    {
        $stats = new Stats_Model_Stats();
        $this->view->sites = $stats->getTopTenSites();
    }
}
