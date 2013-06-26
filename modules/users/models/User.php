<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Model_User extends Zend_Db_Table_Row_Abstract
{
    const ROLE_GUEST = 'guest';
    const ROLE_REGISTERED = 'registered';
    const ROLE_ADMIN = 'admin';

    const AUTH_PASSWORD = 0;
    const AUTH_YUBIKEY = 1;

    private $_image;
    
    /**
    * To identify the app that owns the user obj in the session.
    * Useful when sharing the user between apps.
    */

    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function generateRandomPassword()
    {
        return substr(md5($this->getFullName() . time()), 0, 6);
    }

    /**
    * Password is stored using md5($this->openid.$password) because
    * that's what's used in Zend_OpenId
    */
    public function setPassword($password)
    {
        $this->password = $password;
        $this->password_changed = date('Y-m-d');
    }

    public function setClearPassword($password)
    {
        $this->setPassword(md5($this->openid.$password));
    }

    public function isAllowed($resource, $privilege)
    {
        $acl = Zend_Registry::get('acl');
        return $acl->isAllowed($this->role, $resource, $privilege);
    }

    public static function generateToken()
    {
        $token = '';
        for ($i = 0; $i < 50; $i++) {
            $token .= chr(rand(48, 122));
        }
        
        return md5($token.time());
    }

    public function overrideWithLdapData(Array $ldapData, $syncDb = false)
    {
        $acceptedEula = 1;
        $username = $ldapData['cn'][0];
        $firstname = $ldapData['givenname'][0];
        $lastname = $ldapData['sn'][0];
        $email = $ldapData['mail'][0];

        if (Zend_Registry::get('config')->ldap->admin == $username) {
            $role = Users_Model_User::ROLE_ADMIN;
        } else {
            $role = Users_Model_User::ROLE_REGISTERED;
        }

        if ($this->accepted_eula != $acceptedEula
                || $this->username != $username
                || $this->firstname != $firstname
                || $this->lastname != $lastname
                || $this->email != $email
                || $this->role != $role) {
            $userChanged = true;
        } else {
            $userChanged = false;
        }

        $this->accepted_eula = $acceptedEula;
        $this->username = $username;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->email = $email;
        $this->role = $role;

        if ($syncDb && $userChanged) {
            $this->save();
        }
    }

    public function generateOpenId($baseUrl)
    {
        $config = Zend_Registry::get('config');
        if ($config->subdomain->enabled) {
            $openid = Monkeys_Controller_Action::getProtocol() . '://' . $this->username . '.' . $config->subdomain->hostname;
        } else {
            $openid = $baseUrl . '/identity/' . $this->username;
        }

        if ($config->SSL->enable_mixed_mode) {
            $openid = str_replace('http://', 'https://', $openid);
        }
        Zend_OpenId::normalizeUrl($openid);

        $this->openid = $openid;
    }

    public function createDefaultProfile(Zend_View $view)
    {
        $profiles = new Users_Model_Profiles();
        $profile = $profiles->createRow();
        $profile->user_id = $this->id;
        $profile->name = $view->translate('Default profile');
        $profile->save();

        return $profile->id;
    }

    public function generatePersonalInfo(Array $ldapData, $profileId)
    {
        if (!$this->id) {
            throw new Exception('Can\'t call User::generatePersonalInfo() on an empty User object');
        }

        $ldapConfig = Zend_Registry::get('config')->ldap;
        if (!isset($ldapConfig->fields)) {
            return;
        }

        $fieldValues = new Model_FieldsValues();
        $fields = new Model_Fields();
        foreach ($ldapConfig->fields->toArray() as $openIdField => $ldapField) {
            if (!$fieldRow = $fields->getByOpenIdIdentifier($openIdField)) {
                continue;
            }

            if (!isset($ldapData[$ldapField])) {
                if (strpos($ldapField, '+') == false) {
                    continue;
                }
                $subfields = explode('+', $ldapField);
                array_walk($subfields, 'trim');
                $value = array();
                foreach ($subfields as $subfield) {
                    if (!isset($ldapData[$subfield])) {
                        continue;
                    }
                    $value[] = $ldapData[$subfield][0];
                }
                $value = implode(' ', $value);
            } else {
                $value = $ldapData[$ldapField][0];
            }

            $fieldsValue = $fieldValues->createRow();
            $fieldsValue->user_id = $this->id;
            $fieldsValue->profile_id = $profileId;
            $fieldsValue->field_id = $fieldRow->id;
            $fieldsValue->value = $value;
            $fieldsValue->save();
        }
    }

    public function getImage()
    {
        if (!isset($this->_image)) {
            $images = new Users_Model_SigninImages();
            if (!$row = $images->getForUser($this)) {
                $this->_image = false;
            } else {
                $this->_image = $row;
            }
        }

        return $this->_image;
    }

    public function markSuccessfullLogin()
    {
        $this->last_login = date('Y-m-d H:i:s');
    }

    public function getLastLoginUtc()
    {
        $time = strtotime($this->last_login);
        return gmdate('Y-m-d\TH:i:s\Z', $time);
    }

    public function getSecondsSinceLastLogin()
    {
        return time() - strtotime($this->last_login);
    }
}
