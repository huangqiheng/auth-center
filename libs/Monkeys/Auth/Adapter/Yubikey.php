<?php

class Monkeys_Auth_Adapter_Yubikey implements Zend_Auth_Adapter_Interface
{
    const TOKEN_SIZE  = 32;
    const MIN_IDENTITY_SIZE = 12;

    protected $_options = null;
    protected $_identity = null;
    protected $_password = null;

    public function __construct(array $options = array(), $identity= null, $password = null)
    {
        $this->setOptions($options);
        if ($identity !== null) {
            $this->setIdentity($identity);
        }
        if ($password !== null) {
            $this->setPassword($password);
        }
    }

    public function setOptions($options)
    {
        $this->_options = is_array($options) ? $options : array();
        return $this;
    }

    public function getOptions()
    {
        return $this->_options;
    }

    public function getIdentity()
    {
        return $this->_identity;
    }

    public function setIdentity($identity)
    {
        $this->_identity = (string) $identity;
        return $this;
    }

    public function getPassword()
    {
        return $this->_password;
    }

    public function setPassword($password)
    {
        $this->_password = (string) $password;
        return $this;
    }

    public function setCredential($credential)
    {
        return $this->setPassword($credential);
    }

    public function authenticate()
    {
        if (strlen ($this->_password) < self::TOKEN_SIZE + self::MIN_IDENTITY_SIZE) {
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                $this->_identity,
                array('Provided Yubikey is too short')
            );
        }

        $identity = substr ($this->_password, 0, strlen ($this->_password) - self::TOKEN_SIZE);

        $this->_options['yubiClient'] = new Yubico_Auth(
            $this->_options['api_id'],
            $this->_options['api_key']
        );

        try {
            $auth = $this->_options['yubiClient']->verify($this->_password);
            return new Zend_Auth_Result(
                Zend_Auth_Result::SUCCESS,
                $this->_identity
            );
        } catch (Zend_Exception $e) {
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                $this->_identity,
                array($e->getMessage(), 'Yubico response: ' . $this->_options['yubiClient']->getLastResponse())
            );
        }
    }
}
