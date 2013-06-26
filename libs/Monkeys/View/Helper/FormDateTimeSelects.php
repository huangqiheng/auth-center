<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_View_Helper_FormDateTimeSelects extends Monkeys_View_Helper_FormDateSelects
{
    public function formDateTimeSelects($name, $value = null, $attribs = null,
            $options = null, $listsep = "<br />\n")
    {
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, id, value, attribs, options, listsep, disable
 
        // now start building the XHTML.
        $disabled = '';
        if (true === $disable) {
            $disabled = ' disabled="disabled"';
        }
 
        $elementNamesArray = $this->getDayMonthYearTimeFieldNames($name);
        $valueDay = $valueMonth = $valueYear = $valueHour = $valueMinutes = $valueAmPm = null;
 
        if ($value !== null)
        {
            $valueExploded = explode(' ', $value);

            $dateValueExploded = explode('-', $valueExploded[0]);
            if (!isset($dateValueExploded[2])) {
                $value = null;
            } else {
                $valueDay = (int) $dateValueExploded[2];
                $valueMonth = (int) $dateValueExploded[1];
                $valueYear = (int) $dateValueExploded[0];
            }

            $timeValueExploded = explode(':', $valueExploded[1]);
            $valueHour = $timeValueExploded[0];
            if ($valueHour > 12) {
                $valueHour -= 12;
                $valueAmPm = 'pm';
            } elseif ($valueHour == 0) {
                $valueHour = 12;
                $valueAmPm = 'am';
            } else {
                $valueAmPm = 'am';
            }
            $valueMinutes = (int) $timeValueExploded[1];
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

        // Build the hours next
        $xhtml .= '&nbsp;&nbsp;&nbsp;<select '
        . ' name="' . $this->view->escape($elementNamesArray['hour']) . '"'
        . ' id="' . $this->view->escape($id . '_hour') . '"'
        . $disabled
        . $this->_htmlAttribs($attribs)
        . ">\n    ";

        $list = array();
        if ($options['showEmpty'])
        {
            $list[] = '<option value="-"> </option>';
        }
        for ($i = 1; $i <= 12; $i++)
        {
            $list[] = '<option'
            . ' value="' . $i . '"'
            . ($valueHour === $i ? ' selected="selected"' : '')
            . '>' . sprintf('%02u', $i) . '';
        }
 
        // add the options to the xhtml and close the select
        $xhtml .= implode("\n    ", $list) . "\n</select>";

        // Build the minutes next
        $xhtml .= '<select '
        . ' name="' . $this->view->escape($elementNamesArray['minutes']) . '"'
        . ' id="' . $this->view->escape($id . '_minutes') . '"'
        . $disabled
        . $this->_htmlAttribs($attribs)
        . ">\n    ";

        $list = array();
        if ($options['showEmpty'])
        {
            $list[] = '<option value="-"> </option>';
        }
        for ($i = 0; $i <= 59; $i++)
        {
            $list[] = '<option'
            . ' value="' . $i . '"'
            . ($valueMinutes === $i ? ' selected="selected"' : '')
            . '>' . sprintf('%02u', $i) . '';
        }
 
        // add the options to the xhtml and close the select
        $xhtml .= implode("\n    ", $list) . "\n</select>";


        // Build the ampm next
        $xhtml .= '<select '
        . ' name="' . $this->view->escape($elementNamesArray['ampm']) . '"'
        . ' id="' . $this->view->escape($id . '_ampm') . '"'
        . $disabled
        . $this->_htmlAttribs($attribs)
        . ">\n    ";

        $list = array();
        if ($options['showEmpty'])
        {
            $list[] = '<option value="-"> </option>';
        }
        $list[] = '<option value="am" ' . ($valueAmPm == 'am' ? 'selected="selected"' : '') . '>am';
        $list[] = '<option value="pm" ' . ($valueAmPm == 'pm' ? 'selected="selected"' : '') . '>pm';
 
        // add the options to the xhtml and close the select
        $xhtml .= implode("\n    ", $list) . "\n</select>";
 
        return $xhtml;
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
