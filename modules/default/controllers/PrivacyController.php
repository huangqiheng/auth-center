<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class PrivacyController extends CommunityID_Controller_Action
{
    protected $_numCols = 1;

    public function indexAction()
    {
        $scriptsDir = $this->view->getScriptPath('privacy');

        $locale = Zend_Registry::get('Zend_Locale');
        // render() changes _ to -
        $locale = str_replace('_', '-', $locale);
        $localeElements = explode('-', $locale);

        if (file_exists("$scriptsDir/index-$locale.phtml")) {
            $view = "index-$locale";
        } else if (count($localeElements == 2)
                && file_exists("$scriptsDir/index-".$localeElements[0].".phtml")) {
            $view = 'index-'.$localeElements[0];
        } else {
            $view = 'index-en';
        }

        $this->render($view);
    }
}
