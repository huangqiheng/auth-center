<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

/**
* Based on 
* http://zfsite.andreinikolov.com/2008/05/part-4-zend_form-captcha-password-confirmation-date-selector-field-zend_translate/
*/
class Monkeys_View_Helper_FormDateSelects extends Zend_View_Helper_FormElement
{
    protected $_months = array(
            1   => 'January',
            2   => 'February',
            3   => 'March',
            4   => 'April',
            5   => 'May',
            6   => 'June',
            7   => 'July',
            8   => 'August',
            9   => 'Septembre',
            10  => 'October',
            11  => 'November',
            12  => 'December'
    );

    /**
     * Translation object
     *
     * @var Zend_Translate_Adapter
     */
    protected $_translator;
            
    public function formDateSelects($name, $value = null, $attribs = null,
            $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable
 
        // now start building the XHTML.
        $disabled = '';
        if (true === $disable) {
            $disabled = ' disabled="disabled"';
        }
 
        $elementNamesArray = $this->getDayMonthYearFieldNames($name);
        $valueDay = $valueMonth = $valueYear = null;
 
        if ($value !== null)
        {
            $valueExploded = explode('-', $value);
            if (!isset($valueExploded[2]))
            $value = null;
            else
            {
                $valueDay = (int) $valueExploded[2];
                $valueMonth = (int) $valueExploded[1];
                $valueYear = (int) $valueExploded[0];
            }
        }
 
        // Build the surrounding day element first.
        $xhtml = '<select '
        . ' name="' . $this->view->escape($elementNamesArray['day']) . '"'
        . ' id="' . $this->view->escape($id . '_day') . '"'
        . $disabled
        . $this->_htmlAttribs($attribs)
        . ">\n    ";
 
        // build the list of options
        $list = array();
        if ($options['showEmpty'])
        {
            $list[] = '<option value="-"> </option>';
        }
        for ($i = 1; $i <= 31; $i++)
        {
            $list[] = '<option'
            . ' value="' . $i . '"'
            . ($valueDay === $i ? ' selected="selected"' : '')
            . '>' . $i . '';
        }
 
        // add the options to the xhtml and close the select
        $xhtml .= implode("\n    ", $list) . "\n</select>";
 
        // Build the month next
        $xhtml .= ' <select '
        . ' name="' . $this->view->escape($elementNamesArray['month']) . '"'
        . ' id="' . $this->view->escape($id . '_month') . '"'
        . $disabled
        . $this->_htmlAttribs($attribs)
        . ">\n    ";
 
        // build the list of options
        $list = array();
        if ($options['showEmpty'])
        {
            $list[] = '<option value="-"> </option>';
        }
        for ($i = 1; $i <= 12; $i++)
        {
            $list[] = '<option'
            . ' value="' . $i . '"'
            . ($valueMonth === $i ? ' selected="selected"' : '')
            . '>' . $this->_translateValue($this->_months[$i]) . '';
        }
 
        // add the options to the xhtml and close the select
        $xhtml .= implode("\n    ", $list) . "\n</select>";
 
 
        // Build the years next
        $xhtml .= ' <select '
        . ' name="' . $this->view->escape($elementNamesArray['year']) . '"'
        . ' id="' . $this->view->escape($id . '_year') . '"'
        . $disabled
        . $this->_htmlAttribs($attribs)
        . ">\n    ";
 
        // build the list of options
        $list = array();
        if ($options['showEmpty'])
        {
            $list[] = '<option value="-"> </option>';
        }
 
 
        if ($options['reverseYears'])
        {
            for ($i = $options['endYear']; $i >= $options['startYear']; $i--)
            {
                $list[] = '<option '
                . ' value="' . $i . '"'
                . ($valueYear === $i ? ' selected="selected"' : '')
                . '>' . $i . '</option>';
            }
        }
        else
        {
            for ($i = $options['startYear']; $i >= $options['endYear']; $i++)
            {
                $list[] = '<option '
                . ' value="' . $i . '"'
                . ($valueYear === $i ? ' selected="selected"' : '')
                . '>' . $i . '</option>';
            }            
        }
 
 
        // add the options to the xhtml and close the select
        $xhtml .= implode("\n    ", $list) . "\n</select>";
 
        return $xhtml;
    }
 
 
    /**
     * Makes day, month and year names from given element name. Special case is array notation.
     *
     * Given a value such as foo[bar][baz], the generated names will be
     * foo[bar][baz_day], foo[bar][baz_month] and foo[bar][baz_year]
     *
     * @param  string $value
     * @return array
     */
    protected function getDayMonthYearFieldNames($value)
    {
        if (empty($value) || !is_string($value)) {
            return $value;
        }
 
        $ret = array(
                'day' => $value . '_day',
                'month' => $value . '_month',
                'year' => $value . '_year'
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
                    'year' => $arrayName . '[' . $name . '_year' . ']'
                    );
                }
                return $ret;
    }

    /**
     * Borrowed from multi option value's _translateValue()
     *
     * @param  string $value
     * @return string
     */
    protected function _translateValue($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->_translateValue($val);
            }
            return $value;
        } else {
            if (null !== ($translator = $this->getTranslator())) {
                if ($translator->isTranslated($value)) {
                    return $translator->translate($value);
                }
            }
            return $value;
        }
    }  

    /*
     * Retrieve translation object (borrowed from Zend_View_Helper_HeadTitle)
     *
     * If none is currently registered, attempts to pull it from the registry
     * using the key 'Zend_Translate'.
     *
     * @return Zend_Translate_Adapter|null
     */
    public function getTranslator()
    {
        if (null === $this->_translator) {
            require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Translate')) {
                $this->setTranslator(Zend_Registry::get('Zend_Translate'));
            }
        }
        return $this->_translator;
    }

    /**
     * Sets a translation Adapter for translation (borrowed from Zend_View_Helper_HeadTitle)
     *
     * @param  Zend_Translate|Zend_Translate_Adapter $translate
     * @return Zend_View_Helper_HeadTitle
     */
    public function setTranslator($translate)
    {
        if ($translate instanceof Zend_Translate_Adapter) {
            $this->_translator = $translate;
        } elseif ($translate instanceof Zend_Translate) {
            $this->_translator = $translate->getAdapter();
        } else {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception("You must set an instance of Zend_Translate or Zend_Translate_Adapter");
        }
        return $this;
    }

    protected function _translationsHolder()
    {
        translate('January');
        translate('February');
        translate('March');
        translate('April');
        translate('May');
        translate('June');
        translate('July');
        translate('August');
        translate('Septembre');
        translate('October');
        translate('November');
        translate('December');
    }

}
