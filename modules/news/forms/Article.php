<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class News_Form_Article extends Zend_Form
{
    public function init()
    {
        $title = new Monkeys_Form_Element_Text('title');
        translate('Title');
        $title->setLabel('Title')
              ->setRequired(true)
              ->setAttrib('style', 'width:350px');

        $date = new Monkeys_Form_Element_DateTime('date');
        translate('Publication date');
        $date->setLabel('Publication date')
             ->setShowEmptyValues(false)
             ->setStartEndYear(1900, date('Y') + 1)
             ->setReverseYears(true)
             ->setValue(date('Y-m-d H:i'));

        $excerpt = new Monkeys_Form_Element_Textarea('excerpt');
        translate('Excerpt');
        $excerpt->setLabel('Excerpt')
                ->setAttrib('style', 'width:350px')
                ->setAttrib('rows', 4);

        $content = new Monkeys_Form_Element_Richtextarea('content');
        $content->setDecoratorOptions(array('separateLine' => true))
                ->setAttrib('width', '510px')
                ->setRequired(true);

        $this->addElements(array($title, $date, $excerpt, $content));
    }
}
