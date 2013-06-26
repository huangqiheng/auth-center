<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Ldap
{
    const EXCEPTION_SEARCH = 1;
    const EXCEPTION_GET_ENTRIES = 2;

    private static $_instance;

    private $_ldapConfig;

    /**
    * Ldap link identifier
    */
    private $_dp;

    private $_slappasswd = '/usr/sbin/slappasswd';

    private function __construct()
    {
        $this->_ldapConfig = Zend_Registry::get('config')->ldap;

        if (!$this->_dp= @ldap_connect($this->_ldapConfig->host)) {
            throw new Exception('Could not connect to LDAP server');
        }
        ldap_set_option($this->_dp, LDAP_OPT_PROTOCOL_VERSION, 3);
        if (!@ldap_bind($this->_dp, $this->_ldapConfig->username, $this->_ldapConfig->password)) {
            throw new Exception('Could not bind to LDAP server: ' . ldap_error($this->_dp));
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new Monkeys_Ldap();
        }

        return self::$_instance;
    }


    public function get($dn)
    {
        if (!$resultId = @ldap_search($this->_dp, $dn, "(&(objectClass=*))")) {
            throw new Exception('Could not retrieve record from LDAP server (' . self::EXCEPTION_SEARCH . '): '
                . ldap_error($this->_dp), self::EXCEPTION_SEARCH);
        }

        if (!$result = @ldap_get_entries($this->_dp, $resultId)) {
            throw new Exception('Could not retrieve record from LDAP server (' . self::EXCEPTION_GET_ENTRIES . '): '
                . ldap_error($this->_dp), self::EXCEPTION_GET_ENTRIES);
        }

        return $result[0];
    }

    public function search($baseDn, $field, $value)
    {
        if (!$resultId = @ldap_search($this->_dp, $baseDn, "(&(objectClass=*)($field=$value))")) {
            throw new Exception('Could not retrieve record from LDAP server (' . self::EXCEPTION_SEARCH . '): '
                . ldap_error($this->_dp), self::EXCEPTION_SEARCH);
        }

        if (!$result = @ldap_get_entries($this->_dp, $resultId)) {
            throw new Exception('Could not retrieve record from LDAP server (' . self::EXCEPTION_GET_ENTRIES . '): '
                . ldap_error($this->_dp), self::EXCEPTION_GET_ENTRIES);
        }

        return $result[0];
    }

    public function getAll($baseDn)
    {
        if (!$resultId = @ldap_search($this->_dp, $baseDn, "(&(objectClass=*))")) {
            throw new Exception('Could not retrieve record from LDAP server (' . self::EXCEPTION_SEARCH . '): '
                . ldap_error($this->_dp), self::EXCEPTION_SEARCH);
        }

        if (!$result = @ldap_get_entries($this->_dp, $resultId)) {
            throw new Exception('Could not retrieve record from LDAP server (' . self::EXCEPTION_GET_ENTRIES . '): '
                . ldap_error($this->_dp), self::EXCEPTION_GET_ENTRIES);
        }

        return $result;
    }

    /**
    * lastname (sn) is required for the "inetOrgPerson" schema
    */
    public function add(Users_Model_User $user)
    {
        $dn = 'cn=' . $user->username . ',' . $this->_ldapConfig->baseDn;
        $info = array(
            'cn'            => $user->username,
            'givenName'     => $user->firstname,
            'sn'            => $user->lastname,
            'mail'          => $user->email,
            'userPassword'  => $this->_hashPassword($user->password),
            'objectclass'   => 'inetOrgPerson',
        );
        if (!@ldap_add($this->_dp, $dn, $info) && ldap_error($this->_dp) != 'Success') {
            throw new Exception('Could not add record to LDAP server: ' . ldap_error($this->_dp));
        }
    }

    public function modify(Users_Model_User $user, $newPassword = false)
    {
        $dn = 'cn=' . $user->username . ',' . $this->_ldapConfig->baseDn;
        $info = array(
            'cn'            => $user->username,
            'givenName'     => $user->firstname,
            'sn'            => $user->lastname,
            'mail'          => $user->email,
        );
        if ($newPassword) {
            $info['userPassword'] = $this->_hashPassword($newPassword);
        }
        if (!@ldap_modify($this->_dp, $dn, $info) && ldap_error($this->_dp) != 'Success') {
            throw new Exception('Could not modify record in LDAP server: ' . ldap_error($this->_dp));
        }
    }

    public function modifyUsername(Users_Model_User $user, $oldUsername)
    {
        $dn = 'cn=' . $oldUsername . ',' . $this->_ldapConfig->baseDn;
        $newRdn = 'cn=' . $user->username;
        if (!@ldap_rename($this->_dp, $dn, $newRdn, $this->_ldapConfig->baseDn, true) && ldap_error($this->_dp) != 'Success') {
            throw new Exception('Could not modify username in LDAP server: ' . ldap_error($this->_dp));
        }
    }

    public function delete(Users_Model_User $user)
    {
        $dn = "cn={$user->username}," . $this->_ldapConfig->baseDn;
        if (!@ldap_delete($this->_dp, $dn) && ldap_error($this->_dp) != 'Success') {
            throw new Exception('Could not delete record from LDAP server: ' . ldap_error($this->_dp));
        }
    }

    private function _hashPassword($password)
    {
        if ($algorithm = $this->_ldapConfig->passwordHashing) {
            if (!@is_executable($this->_slappasswd)) {
                throw new Exception($this->_slappasswd . ' doesn\'t exist, or is not executable.');
            }

            $trash = array();
            $password = escapeshellarg($password);
            if (!$returnVar = @exec("{$this->_slappasswd} -h " . '{' . $algorithm . '}' . " -s $password")) {
                throw new Exception("There was a problem executing {$this->_slappasswd}");
            }

            $password = $returnVar;
        }

        return $password;
    }
}
