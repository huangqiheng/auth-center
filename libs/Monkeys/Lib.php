<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Lib
{
    /**
    * Converts the HTML BR tag into plain-text linebreaks
    *
    * Taken from comments on the nl2br function PHP's online manual.
    * "Since nl2br doesn't remove the line breaks when adding in the <br /> tags, it is necessary to strip those off before you convert all of the tags, otherwise you will get double spacing"
    *
    * @access public
    * @static
    * @param string $str
    * @return string
    */
    static function br2nl($str)
    {
        $str = preg_replace("/(\r\n|\n|\r)/", "", $str);
        return preg_replace('=<br */?>=i', "\n", $str);
    }
}
