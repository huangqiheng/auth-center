<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Model_Profiles extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'profiles';
    protected $_primary = 'id';
    protected $_rowClass = 'Users_Model_Profile';

    public function getForUser(Users_Model_User $user)
    {
        $select = $this->select()
            ->where('user_id=?', $user->id);

        return $this->fetchAll($select);
    }
}
