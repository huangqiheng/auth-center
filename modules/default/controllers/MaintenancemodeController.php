<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class MaintenancemodeController extends CommunityID_Controller_Action
{
    public function enableAction()
    {
        $this->_settings->set(Model_Settings::MAINTENANCE_MODE, 1);

        $this->_redirect('');
    }

    public function disableAction()
    {
        $this->_settings->set(Model_Settings::MAINTENANCE_MODE, 0);

        $this->_redirect('');
    }
}
