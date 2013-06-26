<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Upgrade_2_0_0_beta1 extends CommunityID_UpgradeStage
{
    /**
    * I need to fill the new profile_id field in the fields_values table, before being able to
    * add a foreign key to it
    */
    public function proceed()
    {
        $fieldsValues = new Model_FieldsValues();
        $users = new Users_Model_Users();
        foreach ($users->getUsers() as $user) {
            $profileId = $user->createDefaultProfile($this->_view);
            foreach ($fieldsValues->getForUser($user) as $fieldValue) {
                $fieldValue->profile_id = $profileId;
                $fieldValue->save();
            }
        }

        $this->_db->query('ALTER TABLE `fields_values` ADD FOREIGN KEY ( `profile_id` ) REFERENCES `profiles` (`id`) ON DELETE CASCADE');
    }
}
