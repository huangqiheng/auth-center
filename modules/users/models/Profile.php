<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Model_Profile extends Zend_Db_Table_Row_Abstract
{
    public function getFields()
    {
        $fields = new Model_Fields();
        return $fields->getValues($this);
    }
}
