<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Form_OpenIdLogin extends Zend_Form
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

         $openIdIdentity = new Monkeys_Form_Element_Text('openIdIdentity');
         translate('OpenID URL');
         $openIdIdentity->setLabel('OpenID URL')
                        ->setDecoratorOptions(array('dontMarkRequired' => true))
                        ->setAttrib('style', 'width:300px')
                        ->setRequired(false);

        $password = new Monkeys_Form_Element_Password('password');
        translate('Password');
        $password->setLabel('Password')
                 ->setAttrib('style', 'width:300px');

        $yubikey = new Monkeys_Form_Element_Text('yubikey');
        $yubikey->setLabel('YubiKey')
            ->setAttrib('class', 'yubiKeyInput');

        $this->addElements(array($openIdIdentity, $password, $yubikey));

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
                    'dontMarkRequired'  => true,
            ));

            $this->addElement($captcha);
        }
    }
}
