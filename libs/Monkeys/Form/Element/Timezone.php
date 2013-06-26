<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Form_Element_Timezone extends Zend_Form_Element_Select
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

        $fp = fopen(dirname(__FILE__) . '/zone.tab', 'r');
        $timezones = array();
        while ($row = fgets($fp)) {
            if ($row[0] == '#') {
                continue;
            }

            $elements = explode("\t", $row);
            $timezones[trim($elements[2])] = trim(strtr($elements[2], '_', ' '));
        }
        ksort($timezones);

        translate('-- Select a Timezone --');
        $this->addMultiOption(0, '-- Select a Timezone --');
        foreach ($timezones as $key => $value) {
            $this->addMultiOption($key, $value);
        }
    }
}
