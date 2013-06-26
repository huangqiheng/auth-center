<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

class Monkeys_Validate_Password extends Zend_Validate_Abstract
{
    const MSG_DICTIONARY = 'dictionary';
    const MSG_USERNAME = 'username';
    const MSG_LENGTH = 'length';
    const MSG_NUMBERS = 'numbers';
    const MSG_SYMBOLS = 'symbols';
    const MSG_CASE = 'case';

    const MIN_LENGTH_INCLUDED_WORD = 4;

    public $word;
    public $minLength;

    protected $_messageVariables = array(
        'minLength' => 'minLength',
    );

    protected $_messageTemplates = array(
        self::MSG_DICTIONARY => 'Password can\'t be a dictionary word',
        self::MSG_USERNAME => 'Password can\'t contain the username',
        self::MSG_LENGTH => 'Password must be longer than %minLength% characters',
        self::MSG_NUMBERS => 'Password must contain numbers',
        self::MSG_SYMBOLS => 'Password must contain symbols',
        self::MSG_CASE => 'Password needs to have lowercase and uppercase characters',
    );

    private $_username;
    private $_config;

    public function __construct($username = null)
    {
        $this->_username = $username;
        $this->_config = Zend_Registry::get('config');
        $this->minLength = $this->_config->security->passwords->minimum_length;
    }

    public function getPasswordRestrictionsDescription()
    {
        $restrictions = array();

        if ($this->_config->security->passwords->dictionary) {
            $restrictions[] = $this->_messageTemplates[self::MSG_DICTIONARY];
        }

        if ($this->_config->security->passwords->username_different) {
            $restrictions[] = $this->_messageTemplates[self::MSG_USERNAME];
        }

        if ($this->minLength) {
            $restrictions[] = str_replace('%minLength%', $this->minLength, $this->_messageTemplates[self::MSG_LENGTH]);
        }

        if ($this->_config->security->passwords->include_numbers) {
            $restrictions[] = $this->_messageTemplates[self::MSG_NUMBERS];
        }

        if ($this->_config->security->passwords->include_symbols) {
            $restrictions[] = $this->_messageTemplates[self::MSG_SYMBOLS];
        }

        if ($this->_config->security->passwords->lowercase_and_uppercase) {
            $restrictions[] = $this->_messageTemplates[self::MSG_CASE];
        }

        if (!$restrictions) {
            return false;
        }

        return '<ul><li>' . implode('</li><li>', $restrictions) . '</li></ul>';
    }

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
        $isValid = true;

        if ($this->_config->security->passwords->dictionary
                && !$this->_checkDictionary($value, $this->_config->security->passwords->dictionary)) {
            $this->_error(self::MSG_DICTIONARY);
            $isValid = false;
        }

        if ($this->_config->security->passwords->username_different
                && !$this->_checkUsername($value, $context)) {
            $this->_error(self::MSG_USERNAME);
            $isValid = false;
        }

        if ($this->minLength
                && !$this->_checkLength($value, $this->minLength)) {
            $this->_error(self::MSG_LENGTH);
            $isValid = false;
        }

        if ($this->_config->security->passwords->include_numbers
                && !$this->_checkNumbers($value)) {
            $this->_error(self::MSG_NUMBERS);
            $isValid = false;
        }

        if ($this->_config->security->passwords->include_symbols
                && !$this->_checkSymbols($value)) {
            $this->_error(self::MSG_SYMBOLS);
            $isValid = false;
        }

        if ($this->_config->security->passwords->lowercase_and_uppercase
                && !$this->_checkCase($value)) {
            $this->_error(self::MSG_CASE);
            $isValid = false;
        }

        return $isValid;
    }

    private function _checkDictionary($value, $dictionary)
    {
        $value = strtolower($value);
        if (!@$file = fopen(APP_DIR . "/$dictionary", 'r')) {
            throw new Exception('Dictionary file could not be read');
        }

        while (!feof($file)) {
            $word = strtolower(trim(fgets($file)));
            if (strlen($word) >= self::MIN_LENGTH_INCLUDED_WORD
                    && $value == $word) {
                $this->word = $word;
                return false;
            }
        }
        fclose($file);

        return true;
    }

    private function _checkUsername($value, $context)
    {
        $username = '';
        if (is_array($context) && isset($context['username'])) {
            $username = $context['username'];
        } elseif (is_string($context)) {
            $username = $context;
        } elseif ($this->_username) {
            $username = $this->_username;
        } else {
            throw new Exception('Username context was not passed');
        }

        if ($username == '') {
            return true;
        }

        return strpos(strtolower($value), strtolower($username)) === false;
    }

    private function _checkLength($value, $minimumLength)
    {
        return strlen($value) >= $minimumLength;
    }

    private function _checkNumbers($value)
    {
        return preg_match('/[0-9]+/', $value);
    }
    
    private function _checkSymbols($value)
    {
        return preg_match('/[\W]+/', $value);
    }

    private function _checkCase($value)
    {
        return (preg_match('/[A-Z]+/', $value)
                && preg_match('/[a-z]+/', $value));
    }
}
