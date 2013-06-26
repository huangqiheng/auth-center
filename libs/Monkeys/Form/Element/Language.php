<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Form_Element_Language extends Zend_Form_Element_Select
{
    private $_decorator;

    public function __construct($spec, $options = array())
    {
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

    public function init()
    {
        parent::init();

        translate('-- Select a Language --');
        $this->addMultiOption(0, '-- Select a Language --');
        $this->addMultiOptions(Zend_Locale::getTranslationList('language', Zend_Registry::get('Zend_Locale')));
        asort($this->options);
    }
}
