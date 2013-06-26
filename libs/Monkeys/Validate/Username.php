<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

/**
* Validates URL element syntax to avoid encoding issues, according to rfc 1738, section 2.2
*/
class Monkeys_Validate_Username extends Zend_Validate_Abstract
{
    const BAD = 'bad';
    const BAD2 = 'bad2';

    protected $_messageTemplates = array(
        self::BAD => 'Username can only contain US-ASCII alphanumeric characters, plus any of the symbols $-_.+!*\'(), and "',
        self::BAD2 => 'Username is invalid'
    );

    public function isValid($value, $context = null)
    {
        $this->_setValue($value);

        if (!preg_match('/^[A-Za-z\$-_.\+!\*\'\(\)",]+$/', $value)) {
            $this->_error(self::BAD);
            return false;
        }

        $config = Zend_Registry::get('config');
        foreach ($config->security->usernames->exclude as $regex) {
            if (!$regex) {
                continue;
            }
            $regex = preg_quote($regex);
            if (preg_match("/$regex/", $value)) {
                $this->_error(self::BAD2);
                return false;
            }
        }

        return true;
    }
}
