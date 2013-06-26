<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Form_PersonalInfo extends Zend_Form
{
    private $_profile;
    private $_sregRequest;
    private $_sregProps;
    private $_formElements = array();

    public function __construct($options = null, Users_Model_Profile $profile = null, $sregRequest = null, $sregProps = null)
    {
        $this->_profile = $profile;
        $this->_sregRequest= $sregRequest;
        $this->_sregProps = $sregProps;

        $fields = new Model_Fields();
        $fieldsArr = $fields->getValues($this->_profile);
        for ($i = 0; $i < count($fieldsArr); $i++) {
            $this->_formElements[$fieldsArr[$i]->openid] = array(
                'field'     => $fieldsArr[$i],
                'element'   => $fieldsArr[$i]->getFormElement(),
            );
        }

        parent::__construct($options);
    }

    public function init()
    {
        if ($this->_sregProps) {
            foreach ($this->_sregProps as $fieldName => $mandatory) {
                if (isset($this->_formElements[$fieldName])) {
                    $element = $this->_formElements[$fieldName]['element'];
                    if ($mandatory) {
                        // override label
                        $element->setLabel($this->_formElements[$fieldName]['field']->name);
                        $element->setRequired(true);
                    }
                } else {
                    $element = new Monkeys_Form_Element_Text("openid.sreg.$fieldName");
                    $element->setLabel($fieldName);
                    if ($mandatory) {
                        $element->setRequired(true);
                    }
                }

                // user openid standard notation for the field names, instead of
                // our field IDs.
                $element->setName('openid_sreg_' . $fieldName);

                $this->addElement($element);
            }
        } else {
            $profileName = new Monkeys_Form_Element_Text('profileName');
            translate('Profile Name');
            $profileName->setLabel('Profile Name')
                ->setRequired(true)
                ->setValue($this->_profile->name);

            $this->addElement($profileName);

            foreach ($this->_formElements as $formElement) {
                $this->addElement($formElement['element']);
            }
        }
    }

    /**
    * This removes the "openid_sreg_" prefix from the field names
    */
    public function getUnqualifiedValues()
    {
        $values = array();
        foreach ($this->getValues() as $key => $value) {
            $values[substr($key, 12)] = $value;
        }

        return $values;
    }

    public function getSregRequest()
    {
        return $this->_sregRequest;
    }

    public function getPolicyUrl()
    {
        $args  = $this->_sregRequest->getExtensionArgs();

        if (!$args || !isset($args['policy_url'])) {
            return false;
        }

        return $args['policy_url'];
    }

    public static function getForm(Auth_OpenID_Request $request, Users_Model_Profile $profile)
    {
        // The class Auth_OpenID_SRegRequest is included in the following file
        require_once 'libs/Auth/OpenID/SReg.php';

        $sregRequest = Auth_OpenID_SRegRequest::fromOpenIDRequest($request);
        $props = $sregRequest->allRequestedFields();
        $args  = $sregRequest->getExtensionArgs();
        if (isset($args['required'])) {
            $required = explode(',', $args['required']);
        } else {
            $required = false;
        }

        $sregProps = array();
        foreach ($props as $field) {
            $sregProps[$field] = $required && in_array($field, $required);
        }

        $personalInfoForm = new Users_Form_PersonalInfo(null, $profile, $sregRequest, $sregProps);

        return $personalInfoForm;
    }
}
