<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Form_MessageUsers extends Zend_Form
{
    public function init()
    {
        $subject = new Monkeys_Form_Element_Text('subject');
        translate('Subject');
        $subject->setLabel('Subject')
                ->setRequired(true);

        $cc = new Monkeys_Form_Element_Text('cc');
        translate('CC');
        $cc->setLabel('CC');

        $bodyPlain = new Monkeys_Form_Element_Textarea('bodyPlain');
        $bodyPlain->setDecoratorOptions(array('separateLine' => true));

        $bodyHTML= new Monkeys_Form_Element_Richtextarea('bodyHTML');
        $bodyHTML->setDecoratorOptions(array('separateLine' => true))
                 ->setAttrib('width', '510px');

        $this->addElements(array($subject, $cc, $bodyPlain, $bodyHTML));
    }
}
