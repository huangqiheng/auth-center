<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Form_ChangePassword extends Zend_Form
{
    private $_username;

    public function __construct($options = null, $username = null)
    {
        $this->_username = $username;
        parent::__construct($options);
    }

    public function init()
    {
        $password1 = new Monkeys_Form_Element_Password('password1');
        translate('Enter password');
        $passwordValidator = new Monkeys_Validate_Password($this->_username);
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
