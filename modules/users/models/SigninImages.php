<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkeys Ltd.
* @since CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


class Users_Model_SigninImages extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'users_images';
    protected $_primary = 'id';
    protected $_rowClass = 'Users_Model_SigninImage';

    public function getForUser(Users_Model_User $user)
    {
        $select = $this->select()
            ->where('user_id=?', $user->id);

        return $this->fetchRow($select);
    }

    public function getByCookie($cookie)
    {
        $select = $this->select()
            ->where('cookie=?', $cookie);

        return $this->fetchRow($select);
    }

    public function deleteForUser(Users_Model_User $user)
    {
        $where = $this->getAdapter()->quoteInto('user_id=?', $user->id);
        $this->delete($where);
    }

    public function generateCookieId(Users_Model_User $user)
    {
        do {
            $cookie = md5($user->username . rand(1, 1000));
            $select = $this->select()
                ->where('cookie=?', $cookie);
            $row = $this->fetchRow($select);
        } while($row);

        return $cookie;
    }
}

