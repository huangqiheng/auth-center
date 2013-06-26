<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Install_Form_UpgradeLogin extends Zend_Form
{
    public function init()
    {
        $username = new Monkeys_Form_Element_Text('username');
        translate('Username');
        $username->setLabel('Username')
                 ->addValidator(new Monkeys_Validate_Username())
                 ->setRequired(true);

        $password = new Monkeys_Form_Element_Password('password');
        translate('Password');
        $password->setLabel('Password')
                 ->setRequired(true);

        $this->addElements(array($username, $password));
    }
}
