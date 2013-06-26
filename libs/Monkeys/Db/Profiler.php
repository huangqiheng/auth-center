<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Db_Profiler extends Zend_Db_Profiler
{
    public function Monkeys_Db_Profiler()
    {
        parent::__construct(true);
    }

    public function queryStart($queryText, $queryType = null)
    {
        Zend_Registry::get('logger')->log("DB QUERY: $queryText", Zend_Log::DEBUG);
        return parent::queryStart($queryText, $queryType);
    }
}
