<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Form_AccountInfo extends Zend_Form
{
    private $_targetUser;

    public function __construct($options = null, $user = null)
    {
        $this->_targetUser = $user;
        parent::__construct($options);
    }

    public function init()
    {
        $username = new Monkeys_Form_Element_Text('username');
        translate('Username');
        $username->setLabel('Username')
                 ->addValidator(new Monkeys_Validate_Username())
                 ->setRequired(true);

        $firstname = new Monkeys_Form_Element_Text('firstname');
        translate('First Name');
        $firstname->setLabel('First Name')
                  ->setRequired(true);

        $lastname = new Monkeys_Form_Element_Text('lastname');
        translate('Last Name');
        $lastname->setLabel('Last Name')
                 ->setRequired(true);

        $email = new Monkeys_Form_Element_Text('email');
        translate('E-mail');
        $email->setLabel('E-mail')
              ->addFilter('StringToLower')
              ->setRequired(true)
              ->addValidator('EmailAddress');

        $authMethod = new Monkeys_Form_Element_Select('authMethod');
        translate('Auth Method');
        $authMethod->setLabel('Auth Method')
            ->addMultiOption(Users_Model_User::AUTH_PASSWORD, 'Password')
            ->addMultiOption(Users_Model_User::AUTH_YUBIKEY, 'YubiKey')
            ->setAttrib('onchange', 'COMMID.general.toggleYubikey()');

        $yubikey = new Monkeys_Form_Element_Text('yubikey');
        translate('Associated YubiKey');
        $yubikey->setLabel('Associated YubiKey')
            ->setAttrib('class', 'yubiKeyInput');

        $this->addElements(array($username, $firstname, $lastname, $email, $authMethod, $yubikey));

        if (!$this->_targetUser->id) {
            $password1 = new Monkeys_Form_Element_Password('password1');
            translate('Enter password');
            $passwordValidator = new Monkeys_Validate_Password();
            $password1->setLabel('Enter password')
                      ->setRequired(true)
                      ->addValidator(new Monkeys_Validate_PasswordConfirmation())
                      ->addValidator($passwordValidator);

            if ($restrictions = $passwordValidator->getPasswordRestrictionsDescription()) {
                $password1->setDescription($restrictions);
            }

            $password2 = new Monkeys_Form_Element_Password('password2');
            translate('Enter password again');
            $password2->setLabel('Enter password again')
                      ->setRequired(true);

            $this->addElements(array($password1, $password2));
        }
    }
}
