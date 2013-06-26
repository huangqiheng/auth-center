<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Model_Fields extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'fields';
    protected $_primary = 'id';
    protected $_rowClass = 'Model_Field';

    private $_fieldsNames= array();

    public function getAll()
    {
        $select = $this->select();

        return $this->fetchAll($select);
    }

    public function getValues(Users_Model_Profile $profile)
    {
        $select = $this->select()
                       ->setIntegrityCheck(false)
                       ->from('fields')
                       ->joinLeft('fields_values',
                            $this->getAdapter()->quoteInto("fields_values.field_id=fields.id AND fields_values.profile_id=?", $profile->id),
                            array('user_id', 'profile_id', 'field_id', 'value')
                        );

        return $this->fetchAll($select);
    }

    public function getFieldName($fieldIdentifier)
    {
        if (!$this->_fieldsNames) {
            foreach ($this->fetchAll($this->select()) as $field) {
                $this->_fieldsNames[$field->openid] = $field->name;
            }
        }

        return $this->_fieldsNames[$fieldIdentifier];
    }

    public function getByOpenIdIdentifier($openid)
    {
        $select = $this->select()
            ->where('openid=?', $openid);

        return $this->fetchRow($select);
    }

    private function _translationPlaceholders()
    {
        translate('Nickname');
        translate('E-mail');
        translate('Full Name');
        translate('Date of Birth');
        translate('Gender');
        translate('Postal Code');
        translate('Country');
        translate('Language');
        translate('Time Zone');
    }
}
