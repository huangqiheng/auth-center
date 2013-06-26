<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_PersonalinfoController extends CommunityID_Controller_Action
{
    public function preDispatch()
    {
        if ($this->user->role == Users_Model_User::ROLE_ADMIN) {
            throw new Monkeys_AccessDeniedException();
        }
    }

    public function indexAction()
    {
        $profiles = new Users_Model_Profiles();
        $this->view->profiles = $profiles->getForUser($this->user);

        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function editAction()
    {
        $this->view->profile = $this->_getProfile();

        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->personalInfoForm)) {
            $this->view->fields = $appSession->personalInfoForm->getElements();
            unset($appSession->personalInfoForm);
        } else {
            $personalInfoForm = new Users_Form_PersonalInfo(null, $this->view->profile);
            $this->view->fields = $personalInfoForm->getElements();
        }

        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function saveAction()
    {
        $profile = $this->_getProfile();

        $form = new Users_Form_PersonalInfo(null, $profile);
        $formData = $this->_request->getPost();

        $form->populate($formData);
        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->personalInfoForm = $form;
            $this->_forward('edit');
            return;
        }

        $fieldsValues = new Model_FieldsValues();

        if ($this->_getParam('profile')) {
            $fieldsValues->deleteForProfile($profile);
        } else {
            $profile->user_id = $this->user->id;
            $profile->name = $form->getValue('profileName');
            $profile->save();
        }

        foreach ($form->getValues() as $fieldName => $fieldValue) {
            if ($fieldName == 'profileName' || !$fieldValue) {
                continue;
            }

            $fieldsValue = $fieldsValues->createRow();
            $fieldsValue->user_id = $this->user->id;
            $fieldsValue->profile_id = $profile->id;

            list(, $fieldId) = explode('_', $fieldName);
            $fieldsValue->field_id = $fieldId;

            $fieldsValue->value = $fieldValue;

            $fieldsValue->save();
        }

        $this->_helper->FlashMessenger->addMessage($this->view->translate('Profile has been saved'));
        $this->_redirect('/users/personalinfo');
    }

    public function deleteAction()
    {
        $profile = $this->_getProfile();
        if ($profile->id) {
            $profile->delete();
        }

        $this->_helper->FlashMessenger->addMessage($this->view->translate('Profile has been deleted'));
        $this->_redirect('/users/personalinfo');
    }

    private function _getProfile()
    {
        $profiles = new Users_Model_Profiles();

        if (!$this->_getParam('profile')) {
            return $profiles->createRow();
        }

        $profile = $profiles->getRowInstance($this->_getParam('profile'));
        if (!$profile || $profile->user_id != $this->user->id) {
            throw new Monkeys_AccessDeniedException();
        }

        return $profile;
    }
}
