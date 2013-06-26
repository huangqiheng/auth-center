<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

/**
* This class is never called. It's only a placeholder for form error messages wrapped in translate(),
* so that Poedit (or any other message catalogs editor) can catalog these messages for translation
*/
class Form_ErrorMessages
{
    private function _messages()
    {
        translate('Value is empty, but a non-empty value is required');
        translate('Value is required and can\'t be empty');
        translate('\'%value%\' is not a valid email address in the basic format local-part@hostname');
        translate('\'%hostname%\' is not a valid hostname for email address \'%value%\'');
        translate('\'%value%\' does not match the expected structure for a DNS hostname');
        translate('\'%value%\' appears to be a DNS hostname but cannot match TLD against known list');
        translate('\'%value%\' appears to be a local network name but local network names are not allowed');
        translate('Captcha value is wrong');
        translate('Password confirmation does not match');
        translate('Username can only contain US-ASCII alphanumeric characters, plus any of the symbols $-_.+!*\'(), and "');
        translate('Username is invalid');
        translate('The file \'%value%\' was not uploaded');
        translate('Password can\'t be a dictionary word');
        translate('Password can\'t contain the username');
        translate('Password must be longer than %minLength% characters');
        translate('Password must contain numbers');
        translate('Password must contain symbols');
        translate('Password needs to have lowercase and uppercase characters');
    }
}
