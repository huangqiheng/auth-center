<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Authorizations extends Stats_Model_Report
{
    public function getPriority()
    {
        return 2;
    }

    public function getTitle()
    {
        return $this->view->translate('Authorizations per day');
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

        $graph = new Graph(300,200 ,'auto');
        $graph->SetMarginColor('white');
        $graph->SetFrame(false);
        $graph->SetScale("textlin");
        $graph->img->SetMargin(0,30,20,40);
        $graph->yaxis->scale->SetGrace(20);
        $graph->yaxis->HideLabels();
        $graph->yaxis->HideTicks();
        $graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');

        $labelsy = array();
        $datay = array();

        switch ($this->_controllerAction->getRequest()->getParam('type')) {
            case 'year':
                $this->_populateYearData($labelsy, $datay);
                break;
            default:
                $this->_populateWeekData($labelsy, $datay);
        }

        $graph->xaxis->SetTickLabels($labelsy);
        $bplot = new BarPlot($datay);

        $bplot->SetFillGradient("navy","lightsteelblue",GRAD_WIDE_MIDVER);
        $bplot->value->Show();
        $bplot->value->SetFormat('%d');
        $graph->Add($bplot);

        $graph->Stroke();
    }

    private function _populateWeekData(&$labelsy, &$datay)
    {
        $stats = new Stats_Model_Stats();
        $authorizations = $stats->getNumAuthorizationsDays(strtotime('-1 week'), time());

        for ($i = -7; $i < 0; $i++) {
            $time = strtotime("$i days");
            $date = date('Y-m-d', $time);
            $labelsy[] = Stats_Model_Stats::$weekDays[date('w', $time)];
            if (isset($authorizations[$date])) {
                $datay[] = $authorizations[$date]['entry'];
            } else {
                $datay[] = 0;
            }
        }
    }

    private function _populateYearData(&$labelsy, &$datay)
    {
        $stats = new Stats_Model_Stats();
        $firstDayOfMonth = date('Y-' . date('m') . '-01');
        $authorizations = $stats->getNumAuthorizationsYear(strtotime('-11 months', strtotime($firstDayOfMonth)), time());

        for ($i = -11; $i <= 0; $i++) {
            $time = strtotime("$i months");
            $monthNumber = date('n', $time);
            $labelsy[] = Stats_Model_Stats::$months[$monthNumber];
            if (isset($authorizations[$monthNumber])) {
                $datay[] = $authorizations[$monthNumber]['entry'];
            } else {
                $datay[] = 0;
            }
        }
    }
}
