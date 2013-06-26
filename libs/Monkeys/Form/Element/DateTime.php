<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Form_Element_DateTime extends Zend_Form_Element_Xhtml
{
    public $helper = 'formDateTimeSelects';    

    public $options = array();  

    protected $_decorator;

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
        $this->options['showEmpty'] = true;
        $this->options['startYear'] = 1900;        
        $this->options['endYear'] = (int) date("Y");
        $this->options['reverseYears'] = false;
    }
 
    public function setShowEmptyValues($value)
    {
        $this->options['showEmpty'] = (bool) $value;
        return $this;        
    }
 
    public function setStartEndYear($start = null, $end = null)
    {
        if ($start)
        {
            $this->options['startYear'] = (int) $start;            
        }
 
        if ($end)
        {
            $this->options['endYear'] = (int) $end;            
        }
        return $this;        
    }
 
    public function setReverseYears($value)
    {
        $this->options['reverseYears'] = (bool) $value;
        return $this;
    }

    public function isValid($value, $context = null)
    {
        $fieldName = $this->getName();
        $auxiliaryFieldsNames = $this->getDayMonthYearTimeFieldNames($fieldName);
        if (isset($context[$auxiliaryFieldsNames['day']]) && isset($context[$auxiliaryFieldsNames['month']]) 
                && isset($context[$auxiliaryFieldsNames['year']]) && isset($context[$auxiliaryFieldsNames['hour']])
                && isset($context[$auxiliaryFieldsNames['minutes']]) && isset($context[$auxiliaryFieldsNames['ampm']]))
        {
            if ($context[$auxiliaryFieldsNames['year']] == '-' 
                || $context[$auxiliaryFieldsNames['month']] == '-' 
                || $context[$auxiliaryFieldsNames['day']] == '-'
                || $context[$auxiliaryFieldsNames['hour']] == '-'
                || $context[$auxiliaryFieldsNames['minutes']] == '-'
                || $context[$auxiliaryFieldsNames['ampm']] == '-')
            {
                $value = null;
            }
            else
            {
                $hour = $context[$auxiliaryFieldsNames['hour']];
                if ($context[$auxiliaryFieldsNames['ampm']] == 'pm') {
                    $hour += 12;
                }
                $value = str_pad($context[$auxiliaryFieldsNames['year']], 4, '0', STR_PAD_LEFT) . '-'
                    . str_pad($context[$auxiliaryFieldsNames['month']], 2, '0', STR_PAD_LEFT) . '-'
                    . str_pad($context[$auxiliaryFieldsNames['day']], 2, '0', STR_PAD_LEFT) . ' '
                    . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':'
                    . str_pad($context[$auxiliaryFieldsNames['minutes']], 2, '0', STR_PAD_LEFT) . ':00';
            }
 
            $this->setValue($value);
        }
 
        return parent::isValid($value, $context);
    }

    protected function getDayMonthYearTimeFieldNames($value)
    {
        if (empty($value) || !is_string($value)) {
            return $value;
        }
 
        $ret = array(
                'day' => $value . '_day',
                'month' => $value . '_month',
                'year' => $value . '_year',
                'hour' => $value . '_hour',
                'minutes' => $value . '_minutes',
                'ampm' => $value . '_ampm',
                );
 
        if (strstr($value, '['))
        {
            $endPos = strlen($value) - 1;
            if (']' != $value[$endPos]) {
                return $ret;
            }
 
            $start = strrpos($value, '[') + 1;
            $name = substr($value, $start, $endPos - $start);
            $arrayName = substr($value, 0, $start-1);
            $ret = array(
                    'day' => $arrayName . '[' . $name . '_day' . ']',
                    'month' => $arrayName . '[' . $name . '_month'  . ']',
                    'year' => $arrayName . '[' . $name . '_year' . ']',
                    'hour' => $arrayName . '[' . $name . '_hour' . ']',
                    'minutes' => $arrayName . '[' . $name . '_minutes' . ']',
                    'ampm' => $arrayName . '[' . $name . '_ampm' . ']',
                    );
        }
        return $ret;
    }
}
