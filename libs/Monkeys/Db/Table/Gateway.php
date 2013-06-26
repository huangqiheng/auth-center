<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

abstract class Monkeys_Db_Table_Gateway extends Zend_Db_Table_Abstract
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    public function getRowInstance($id)
    {
        return $this->find($id)->current();
    }

    public function getRandomRowInstance(Projects_Model_Project $project)
    {
        $select = $this->select()->where('project_id=?', $project->id)->order(new Zend_Db_Expr('RAND()'));

        return $this->fetchRow($select);
    }
}
