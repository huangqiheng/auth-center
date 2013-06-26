<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Install_Form_Install extends Zend_Form
{
    public function init()
    {
        $hostname = new Monkeys_Form_Element_Text('hostname');
        translate('Hostname');
        translate('usually localhost');
        $hostname->setLabel('Hostname')
                 ->setDescription('usually localhost')
                 ->setRequired(true)
                 ->setDecoratorOptions(array('dontMarkRequired' => true))
                 ->setValue('localhost');

        $dbname = new Monkeys_Form_Element_Text('dbname');
        translate('Database name');
        $dbname->setLabel('Database name')
               ->setRequired(true)
               ->setDecoratorOptions(array('dontMarkRequired' => true))
               ->setValue(Zend_Registry::get('config')->database->params->dbname);

        $dbusername = new Monkeys_Form_Element_Text('dbusername');
        translate('Database username');
        $dbusername->setLabel('Database username')
                   ->setRequired(true)
                   ->setDecoratorOptions(array('dontMarkRequired' => true));

        $dbpassword = new Monkeys_Form_Element_Password('dbpassword');
        translate('Database password');
        $dbpassword->setLabel('Database password');

        $supportemail = new Monkeys_Form_Element_Text('supportemail');
        translate('Support E-mail');
        translate('Will be used as the sender for any message sent by the system, and as the recipient for user feedback');
        $supportemail->setLabel('Support E-mail')
                     ->setDescription('Will be used as the sender for any message sent by the system, and as the recipient for user feedback')
                     ->addFilter('StringToLower')
                     ->addValidator('EmailAddress')
                     ->setRequired(true)
                     ->setDecoratorOptions(array('dontMarkRequired' => true));

        $username = new Monkeys_Form_Element_Text('username');
        $username->setLabel('Username')
                 ->setRequired(true)
                 ->setDecoratorOptions(array('dontMarkRequired' => true));

        $password1 = new Monkeys_Form_Element_Password('password1');
        translate('Enter password');
        $passwordValidator = new Monkeys_Validate_Password();
        $password1->setLabel('Enter password')
                  ->setRequired(true)
                  ->setDecoratorOptions(array('dontMarkRequired' => true))
                  ->addValidator(new Monkeys_Validate_PasswordConfirmation())
                  ->addValidator($passwordValidator);

        if ($restrictions = $passwordValidator->getPasswordRestrictionsDescription()) {
            $password1->setDescription($restrictions);
        }

        $password2 = new Monkeys_Form_Element_Password('password2');
        translate('Enter password again');
        $password2->setLabel('Enter password again')
                  ->setRequired(true)
                  ->setDecoratorOptions(array('dontMarkRequired' => true));
            

        $this->addElements(array($hostname, $dbname, $dbusername, $dbpassword, $supportemail,
            $username, $password1, $password2));
    }
}
