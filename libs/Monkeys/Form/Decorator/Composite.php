<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Form_Decorator_Composite extends Zend_Form_Decorator_Abstract
        implements Zend_Form_Decorator_Marker_File_Interface // to avoid Zend_Form_Element_File to whine
{
    public function buildLabel()
    {
        $element = $this->getElement();
        $label = $element->getLabel();
        if ($label == '') {
            return false;
        }

        if ($translator = $element->getTranslator()) {
            $label = $translator->translate($label);
        }
        if ($element->isRequired() && !$this->getOption('dontMarkRequired')) {
            $label .= '*';
        }

        return $label . ':';
        /*return $element->getView()
                       ->formLabel($element->getName(), $label);*/
    }

    public function buildInput($content = '')
    {
        $element = $this->getElement();
        $helper  = $element->helper;
        $attribs = $element->getAttribs();
        if ($this->getOption('bottom')) {
            $attribs = array_merge($attribs, array('style' => 'top:0; width:auto'));
        }

        if ($element instanceof Monkeys_Form_Element_Captcha) {
            return $content;
        }

        $input = $element->getView()->$helper(
            $element->getName(),
            $element->getValue(),
            $attribs,
            $element->options,
            $this->getSeparator()
        );

        if ($element instanceof Monkeys_Form_Element_Radio) {
            return "<div class=\"formRadio\">$input</div>";
        }

        return $input;
    }

    public function buildErrors()
    {
        $element  = $this->getElement();
        $messages = $element->getMessages();
        if (empty($messages)) {
            return '';
        }
        
        return $element->getView()->formErrors($messages);
        /*return '<div class="errors">' .
               $element->getView()->formErrors($messages) . '</div>';*/
    }

    public function buildDescription()
    {
        $element = $this->getElement();
        $desc    = $element->getDescription();
        if (empty($desc)) {
            return '';
        }
        if ($translator = $element->getTranslator()) {
            $desc = $translator->translate($desc);
        }

        return $desc;
    }

    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }
        if (null === $element->getView()) {
            return $content;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $label     = $this->buildLabel();
        $input     = $this->buildInput($content);
        $errors    = $this->buildErrors();
        $desc      = $this->buildDescription();

        if ($desc && $errors) {
            $desc = "<div>$desc</div>";
        } else if ($desc && !$errors) {
            $desc = "<div class=\"description\">$desc</div>";
        }

        if ($this->getOption('yuiGridType')) {
            $yuiGridType = $this->getOption('yuiGridType');
        } else {
            $yuiGridType = 'gf';
        }

        if ($this->getOption('wideLabel')) {
            if ($label !== false) {
                $output = "<div class=\"yui-$yuiGridType\" style=\"padding-bottom:10px\">\n"
                         ."     <div class=\"formLabel\" style=\"padding-bottom:10px\">$label</div>\n"
                         ."     <div class=\"yui-u first\">&nbsp;</div>\n"
                         ."     <div class=\"yui-u\">\n"
                         ."          $input\n"
                         ."          $desc\n"
             . ($errors? "          <div>$errors</div>\n" : "")
                         ."     </div>\n"
                         ."</div>\n";
            } else {
                $output = "<div style=\"padding-bottom:10px\">\n"
                         ."      $input\n"
                         ."      $desc\n"
              . ($errors? "      <div>$errors</div>\n" : "")
                         ."</div>\n";
            }
       } else if ($this->getOption('separateLine')) {
            $output = "<div class=\"yui-$yuiGridType\" style=\"font-weight:bold\">\n"
                     ."     $label\n"
                     ."</div>\n"
                     ."<div>$input</div>\n"
                     ."<div>$desc</div>\n"
          . ($errors? "<div>$errors</div>\n" : "");
        } else if ($this->getOption('continuous')) {
            $output = "<div style=\"padding-bottom:10px\">\n"
                     ."     <span class=\"formLabel\">$label</span> $input"
                     ."     <div>\n"
                     ."          $desc\n"
          . ($errors? "          <div>$errors</div>\n" : "")
                     ."     </div>\n"
                     ."</div>\n";
        } else {
            $output = "<div class=\"yui-$yuiGridType\">\n"
                     ."     <div class=\"yui-u first\">$label</div>\n"
                     ."     <div class=\"yui-u\">\n"
                     ."          $input\n"
                     ."          $desc\n"
          . ($errors? "          <div>$errors</div>\n" : "")
                     ."     </div>\n"
                     ."</div>\n";
        }

        return $output;

        // I believe we shouldn't use $placement (messes up the captcha, but I'm not sure about radios)
        /*switch ($placement) {
            case (self::PREPEND):
                return $output . $separator . $content;
            case (self::APPEND):
            default:
                return $content . $separator . $output;
        }*/
    }
}

