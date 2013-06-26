<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Form_SigninImage extends Zend_Form
{
    public function init()
    {
        $image = new Monkeys_Form_Element_File('image');
        $image->setLabel('')
              ->setRequired(true)
              ->setDescription('Only files of type jpg, jpeg, png and gif are allowed.<br />Maximum size is 2 MB.')
             ->addValidator('Count', false, 1)
             ->addValidator('Size', false, 2097152) // 2 MB
             ->addValidator('Extension', false, 'jpg, jpeg, png, gif')
             ->addFilter('StripNewlines');    // just a hack to circumvent ZF bug
        translate('Only files of type jpg, jpeg, png and gif are allowed.<br />Maximum size is 2 MB.');

        $this->addElements(array($image));
    }
}

