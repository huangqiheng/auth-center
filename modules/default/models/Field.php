<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Model_Field extends Zend_Db_Table_Row_Abstract
{
    const TYPE_TEXT     = 1;
    const TYPE_DATE     = 2;
    const TYPE_GENDER   = 3;
    const TYPE_COUNTRY  = 4;
    const TYPE_LANGUAGE = 5;
    const TYPE_TIMEZONE = 6;
    const TYPE_EMAIL    = 7;

    public function getFormElement()
    {
        $varname = 'field_' . $this->id;

        switch ($this->type) {
            case self::TYPE_TEXT:
                $el = new Monkeys_Form_Element_Text($varname);
                break;
            case self::TYPE_DATE:
                $el = new Monkeys_Form_Element_Date($varname);
                 $el->addValidator('date', false, array('format_type' => 'Y-m-d'))
                    ->setShowEmptyValues(true)
                    ->setStartEndYear(1900, date('Y') - 7)
                    ->setReverseYears(true);
                break;
            case self::TYPE_GENDER:
                translate('Male');
                translate('Female');
                $el = new Monkeys_Form_Element_Radio($varname);
                $el->setSeparator('&nbsp;&nbsp')
                   ->addMultiOption('M', 'Male')
                   ->addMultiOption('F', 'Female');
                break;
            case self::TYPE_COUNTRY:
                $el = new Monkeys_Form_Element_Country($varname);
                break;
            case self::TYPE_LANGUAGE:
                $el = new Monkeys_Form_Element_Language($varname);
                break;
            case self::TYPE_TIMEZONE:
                $el = new Monkeys_Form_Element_Timezone($varname);
                break;
            case self::TYPE_EMAIL:
                $el = new Monkeys_Form_Element_Text($varname);
                $el->addValidator('EmailAddress');
                break;
            default:
                throw new Exception('Unknown field type: ' . $this->type);
                break;
        }
        $el->setLabel($this->name);
        $el->setValue($this->value);

        return $el;
    }
}
