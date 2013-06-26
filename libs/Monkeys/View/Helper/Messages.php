<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_View_Helper_Messages
{
    public function messages($messages)
    {
        if ($messages) {
            $str = "<div id=\"messages\">\n";
            $str .= implode('<br />', $messages);
            $str .= "</div>\n";
        } else {
            $str = '';
        }

        return $str;
    }
}
