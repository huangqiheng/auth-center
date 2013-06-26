<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

/**
* I was obliged to do this because of a mayor flaw of Zend_Form_Element_Captcha
* not letting using custom captcha adapters
* (if the adapter is not defined in the construct, an exception is thrown, so I don't
* even have a chance to call addPrefixPath() on the element...)
*/
class Monkeys_Form_Element_Captcha extends Zend_Form_Element_Captcha
{
    private $_decorator;

    public function __construct($spec, $options = null)
    {
        $this->addPrefixPath('Monkeys_Captcha', 'Monkeys/Captcha/', 'captcha');
        $options = array_merge($options, array('disableLoadDefaultDecorators' =>true));
        parent::__construct($spec, $options);

        $this->_decorator = new Monkeys_Form_Decorator_Composite();
        $this->addDecorator($this->_decorator);
    }

    public function setDecoratorOptions(array $options)
    {
        $this->_decorator->setOptions($options);

        return $this;
    }
}
