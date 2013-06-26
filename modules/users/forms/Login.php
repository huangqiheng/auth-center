<?php

class Users_Form_Login extends Zend_Form
{
    private $_baseWebDir;
    private $_useCaptcha;

    public function __construct($options = null, $baseWebDir = null, $useCaptcha= false)
    {
        $this->_baseWebDir = $baseWebDir;
        $this->_useCaptcha = $useCaptcha;
        parent::__construct($options);
    }

    public function init()
    {
        $username = new Monkeys_Form_Element_Text('username');
        translate('USERNAME');
        $username->setLabel('USERNAME')
                 ->setDecoratorOptions(array(
                    'separateLine'      => true,
                    'dontMarkRequired'  => true,
                 ))
                 ->setRequired(true);

        $password = new Monkeys_Form_Element_Password('password');
        translate('PASSWORD');
        $password->setLabel('PASSWORD')
                 ->setDecoratorOptions(array(
                    'separateLine'      => true,
                 ));

        $yubikey = new Monkeys_Form_Element_Text('yubikey');
        $yubikey->setLabel('YUBIKEY')
             ->setDecoratorOptions(array(
                'separateLine'      => true,
             ))
            ->setAttrib('class', 'yubiKeyInput');

        $rememberme = new Monkeys_Form_Element_Checkbox('rememberme');
        $rememberme->setLabel('Remember me');

        $this->addElements(array($username, $password, $yubikey, $rememberme));

        if ($this->_useCaptcha) {
            $captcha = new Monkeys_Form_Element_Captcha('captcha', array(
                'label'     => 'Please enter the text below',
                'captcha'   => array(
                    'captcha'       => 'Image',
                    'sessionClass'  => get_class(Zend_Registry::get('appSession')),
                    'font'          => APP_DIR . '/libs/Monkeys/fonts/Verdana.ttf',
                    'imgDir'        => WEB_DIR. '/captchas',
                    'imgUrl'        => $this->_baseWebDir . '/captchas',
                    'wordLen'       => 4,
                    'fontSize'      => 30,
                    'timeout'       => 300,
                )
            ));
            $captcha->setDecoratorOptions(array(
                    'separateLine'      => true,
                    'dontMarkRequired'  => true,
            ));

            $this->addElement($captcha);
        }
    }
}
