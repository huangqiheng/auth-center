<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class ErrorController extends Monkeys_Controller_Error
{
    protected function _getTranslationForException($ex)
    {
        switch ($ex) {
            case 'Monkeys_BadUrlException':
                return $this->view->translate('The URL you entered is incorrect. Please correct and try again.');
                break;
            case 'Monkeys_AccessDeniedException':
                return $this->view->translate('Access Denied - Maybe your session has expired? Try logging-in again.');
                break;
            default:
                return $ex;
        }
    }
}
