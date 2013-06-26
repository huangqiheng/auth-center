<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class ProfileController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        $this->view->queryString = $this->_queryString();

        $server = $this->_getOpenIdProvider();
        $request = $server->decodeRequest();

        $this->view->fields = array();
        $this->view->policyUrl = false;

        $profiles = new Users_Model_Profiles();
        $this->view->profiles = $profiles->getForUser($this->user);
        $requestedProfileId = $this->_getParam('profile');
        foreach ($this->view->profiles as $profile) {
            if ($requestedProfileId == 0 || $requestedProfileId == $profile->id) {
                $this->view->profileId = $profile->id;
                $personalInfoForm = Users_Form_PersonalInfo::getForm($request, $profile);
                $this->view->fields = $personalInfoForm->getElements();
                if ($personalInfoForm->getPolicyUrl()) {
                    $this->view->policyUrl = $personalInfoForm->getPolicyUrl();
                }
                break;
            }
        }
        //$this->view->profiles->rewind();
    }
}
