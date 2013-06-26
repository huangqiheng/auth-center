<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class IndexController extends CommunityID_Controller_Action
{
    const NEWS_NUMBER = 4;

    public function indexAction()
    {
        $scriptsDir = $this->view->getScriptPaths();

        $locale = Zend_Registry::get('Zend_Locale');
        // render() changes _ to -
        $locale = str_replace('_', '-', $locale);
        $localeElements = explode('-', $locale);

        $view = false;
        foreach ($scriptsDir as $scriptDir) {
            if (file_exists($scriptDir."index/subheader-$locale.phtml")) {
                $view = "subheader-$locale";
                break;
            } else if (count($localeElements == 2)
                    && file_exists($scriptDir."index/subheader-".$localeElements[0].".phtml")) {
                $view = 'subheader-'.$localeElements[0];
                break;
            }
        }
        if (!$view) {
            $view = 'subheader-en';
        }

        $this->getResponse()->insert('subHeader', $this->view->render("index/$view.phtml"));

        $this->_helper->actionStack('index', 'login', 'users');

        $news = new News_Model_News();
        $this->view->news = $news->getLatest(self::NEWS_NUMBER, $this->user);

        $view = false;
        foreach ($scriptsDir as $scriptDir) {
            if (file_exists($scriptDir."index/index-$locale.phtml")) {
                $view = "index-$locale";
                break;
            } else if (count($localeElements == 2)
                    && file_exists($scriptDir."index/index-".$localeElements[0].".phtml")) {
                $view = 'index-'.$localeElements[0];
                break;
            }
        }
        if (!$view) {
            $view = 'index-en';
        }

        $this->render($view);
    }
}
