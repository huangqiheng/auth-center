<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

require_once WEB_DIR . '/fckeditor/fckeditor.php';

class Monkeys_View_Helper_FormRichtextarea extends Zend_View_Helper_FormElement
{
    public function formRichtextarea($name, $value = null, $attribs = null, $options = null, $listSep = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        $fck = new FCKEditor($this->view->escape($name));
        $fck->BasePath = $this->view->base . '/fckeditor/';
        $fck->Value = $value;
        if (isset($attribs['width'])) {
            $fck->Width = $attribs['width'];
        } else {
            $fck->Width = '890';
        }
        $fck->Height = '600';
        $fck->Config['CustomConfigurationsPath'] = '../../javascript/fck_custom_config.js';
        $fck->ToolbarSet = 'MonkeysToolbar';
        return $fck->CreateHtml();
    }
}
