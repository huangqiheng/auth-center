<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Model_Settings extends Monkeys_Db_Table_Gateway
{
    protected $_name = 'settings';
    protected $_primary = 'name';

    const MAINTENANCE_MODE = 'maintenance_mode';
    const VERSION = 'version';

    public function get($name)
    {
        $select = $this->select()
                       ->where('name=?', $name);

        $row = $this->fetchRow($select);

        if (!$row) {
            return null;
        }

        return $row->value;
    }

    public function set($name, $value)
    {
        $this->update(array('value' => $value), $this->getAdapter()->quoteInto('name=?', $name));
    }

    public function isMaintenanceMode()
    {
        return $this->get(self::MAINTENANCE_MODE);
    }

    public function getVersion()
    {
        return $this->get(self::VERSION);
    }
}
