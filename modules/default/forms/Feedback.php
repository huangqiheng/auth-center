<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Form_Feedback extends Zend_Form
{
    private $_baseWebDir;

    public function __construct($options = null, $baseWebDir = null)
    {
        $this->_baseWebDir = $baseWebDir;
        parent::__construct($options);
    }

    public function init()
    {
        $name = new Monkeys_Form_Element_Text('name');
        translate('Enter your name');
        $name->setLabel('Enter your name')
             ->setRequired(true);
        
        $email = new Monkeys_Form_Element_Text('email');
        translate('Enter your E-mail');
        $email->setLabel('Enter your E-mail')
              ->addFilter('StringToLower')
              ->setRequired(true)
              ->addValidator('EmailAddress');

        $feedback = new Monkeys_Form_Element_Textarea('feedback');
        translate('Enter your questions or comments');
        $feedback->setLabel('Enter your questions or comments')
                 ->setRequired(true)
                 ->setAttrib('cols', 60)
                 ->setAttrib('rows', 4);

        // ZF has some bugs when using mutators here, so I have to use the config array
        translate('Please enter the text below');
        $captcha = new Monkeys_Form_Element_Captcha('captcha', array(
            'label'     => 'Please enter the text below',
            'captcha'   => array(
                'captcha'       => 'Image',
                'sessionClass'  => get_class(Zend_Registry::get('appSession')),
                'font'          => APP_DIR . '/libs/Monkeys/fonts/Verdana.ttf',
                'imgDir'        => WEB_DIR . '/captchas',
                'imgUrl'        => $this->_baseWebDir . '/captchas',
                'wordLen'       => 4,
                'fontSize'      => 30,
                'timeout'       => 300,
            )
        ));

        $this->addElements(array($name, $email, $feedback, $captcha));
    }
}
