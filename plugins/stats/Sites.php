<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Sites extends Stats_Model_Report
{
    public function getPriority()
    {
        return 3;
    }

    public function getTitle()
    {
        return $this->view->translate('Trusted Sites');
    }

    public function setTemplateVars()
    {
        $this->view->weekSelected = '';
        $this->view->yearSelected = '';

        switch ($this->_controllerAction->getRequest()->getParam('type')) {
            case 'year':
                $this->view->yearSelected = 'selected="true"';
                $this->view->type = 'year';
                break;
            default:
                $this->view->weekSelected = 'selected="true"';
                $this->view->type = 'week';
        }

        $this->view->rand = rand(0, 1000);
    }

    public function renderGraph()
    {
        require_once 'libs/jpgraph/jpgraph.php';
        require_once 'libs/jpgraph/jpgraph_bar.php';
        require_once 'libs/jpgraph/jpgraph_line.php';

        $graph = new Graph(300,200 ,'auto');
        $graph->SetMarginColor('white');
        $graph->SetFrame(false);
        $graph->SetScale("textlin");
        $graph->SetY2Scale("lin");
        $graph->img->SetMargin(0,30,20,65);
        $graph->yaxis->HideLabels();
        $graph->yaxis->HideTicks();
        $graph->yaxis->scale->SetGrace(20);
        $graph->y2axis->SetColor("black","red");
        $graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');

        $labelsy = array();
        $datay = array();
        $datay2 = array();

        switch ($this->_controllerAction->getRequest()->getParam('type')) {
            case 'year':
                $this->_populateYearData($labelsy, $datay, $datay2);
                break;
            default:
                $this->_populateWeekData($labelsy, $datay, $datay2);
        }

        $graph->xaxis->SetTickLabels($labelsy);

        $locale = Zend_Registry::get('Zend_Locale');
        if ($locale == 'ja') {
            // the ttf file for FF_MINCHO is already encoded in utf-8
            $legend1 = $this->view->translate('Trusted sites');
            $legend2 = $this->view->translate('Sites per user');
        } else {
            // default ttf files are latin-1 encoded
            $legend1 = utf8_decode($this->view->translate('Trusted sites'));
            $legend2 = utf8_decode($this->view->translate('Sites per user'));
        }
        $bplot = new BarPlot($datay);
        $bplot->setLegend($legend1);
        $bplot->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
        $bplot->value->Show();
        $bplot->value->SetFormat('%d');

        $p1 = new LinePlot($datay2);
        $p1->SetColor("red");
        $p1->SetLegend($legend2);

        $graph->Add($bplot);
        $graph->AddY2($p1);

        $graph->legend->SetLayout(LEGEND_HOR);
        if ($locale == 'ja') {
            $graph->legend->setFont(FF_MINCHO, FS_NORMAL);
        }
        $graph->legend->Pos(0.5,0.99,"center","bottom");

        $graph->Stroke();
    }

    private function _populateWeekData(&$labelsy, &$datay, &$datay2)
    {
        $stats = new Stats_Model_Stats();
        $initialTrustedSites = $stats->getNumTrustedSites(strtotime('-1 week'));
        $initialRegisteredUsers = $stats->getNumRegisteredUsers(strtotime('-1 week'));

        $sites = $stats->getNumTrustedSitesDays(strtotime('-1 week'), time());
        $numUsers = $stats->getNumRegisteredUsersDays(strtotime('-1 week'), time());

        for ($i = -7; $i < 0; $i++) {
            $time = strtotime("$i days");
            $date = date('Y-m-d', $time);
            $labelsy[] = Stats_Model_Stats::$weekDays[date('w', $time)];

            if (isset($sites[$date])) {
                $sitesPeriod = $sites[$date]['site'];
            } else {
                $sitesPeriod = 0;
            }

            if (isset($numUsers[$date])) {
                $usersPeriod = $numUsers[$date]['users'];
            } else {
                $usersPeriod = 0;
            }

            if ($i > -7) {
                $datay[] = $datay[$i + 6] + $sitesPeriod;
                $datay2[] = $datay2[$i + 6] + $usersPeriod;
            } else {
                $datay[] = $initialTrustedSites + $sitesPeriod;
                $datay2[] = $initialRegisteredUsers + $usersPeriod;
            }
        }

        for ($i = 0; $i < count($datay2); $i++) {
            if ($datay2[$i] > 0) {
                $datay2[$i] = round($datay[$i] / $datay2[$i], 2);
            }
        }
    }

    private function _populateYearData(&$labelsy, &$datay, &$datay2)
    {
        $stats = new Stats_Model_Stats();
        $initialTrustedSites = $stats->getNumTrustedSites(strtotime('-1 week'));
        $initialRegisteredUsers = $stats->getNumRegisteredUsers(strtotime('-1 week'));

        $firstDayOfMonth = date('Y-' . date('m') . '-01');

        $sites = $stats->getNumTrustedSitesYear(strtotime('-11 months', strtotime($firstDayOfMonth)), time());
        $numUsers = $stats->getNumRegisteredUsersYear(strtotime('-1 week'), time());

        for ($i = -11; $i <= 0; $i++) {
            $time = strtotime("$i months");
            $monthNumber = date('n', $time);
            $labelsy[] = Stats_Model_Stats::$months[$monthNumber];

            if (isset($sites[$monthNumber])) {
                $sitesPeriod = $sites[$monthNumber]['site'];
            } else {
                $sitesPeriod = 0;
            }

            if (isset($numUsers[$monthNumber])) {
                $usersPeriod = $numUsers[$monthNumber]['users'];
            } else {
                $usersPeriod = 0;
            }

            if ($i > -11) {
                $datay[] = $datay[$i + 10] + $sitesPeriod;
                $datay2[] = $datay2[$i + 10] + $usersPeriod;
            } else {
                $datay[] = $initialTrustedSites + $sitesPeriod;
                $datay2[] = $initialRegisteredUsers + $usersPeriod;
            }
        }

        for ($i = 0; $i < count($datay2); $i++) {
            if ($datay2[$i] > 0) {
                $datay2[$i] = round($datay[$i] / $datay2[$i], 2);
            }
        }
    }
}
