<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Application_Module_Autoloader extends Zend_Application_Module_Autoloader
{
    public function __construct($options)
    {
        parent::__construct($options);
        $this->addResourceType('controllerHelpers', 'controllers', 'Controller');
    }
}
