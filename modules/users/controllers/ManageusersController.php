<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Users_ManageusersController extends CommunityID_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->actionStack('index', 'login', 'users');
    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNeverRender(true);

        if ($this->_config->ldap->enabled && $this->_config->ldap->keepRecordsSynced) {
            $ldap = Monkeys_Ldap::getInstance();
            $ldap->delete($this->targetUser);
        }

        $this->targetUser->delete();

        echo $this->view->translate('User has been deleted successfully');
    }

    public function deleteunconfirmedAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);

        $users = new Users_Model_Users();
        $users->deleteUnconfirmed($this->_getParam('olderthan'));
    }

    public function sendreminderAction()
    {
        $this->_helper->viewRenderer->setNeverRender(true);

        $users = new Users_Model_Users();
        foreach ($users->getUnconfirmedUsers($this->_getParam('olderthan')) as $user) {
            $mail = self::getMail($user, $this->view->translate('Community-ID registration reminder'));
            try {
                $mail->send();
                $this->_increaseReminderCount($user);
            } catch (Zend_Mail_Exception $e) {
                Zend_Registry::get('logger')->log($e->getMessage(), Zend_Log::ERR);
                if (!$this->_config->environment->production) {
                    // still increase the reminder counter when testing
                    $this->_increaseReminderCount($user);
                }
            }
        }
    }

    private function _increaseReminderCount(Users_Model_User $user)
    {
        $user->reminders++;
        $user->save();
    }

    /**
    * @return Zend_Mail
    * @throws Zend_Mail_Protocol_Exception
    */
    public static function getMail(Users_Model_User $user, $subject)
    {
        $file = CommunityID_Resources::getResourcePath('reminder_mail.txt');
        $emailTemplate = file_get_contents($file);
        $emailTemplate = str_replace('{userName}', $user->getFullName(), $emailTemplate);

        $currentUrl = Zend_OpenId::selfURL();
        preg_match('#(.*)/manageusers/sendreminder#', $currentUrl, $matches);
        $emailTemplate = str_replace('{registrationURL}', $matches[1] . '/register/eula?token=' . $user->token, $emailTemplate);

        // can't use $this->_config 'cause it's a static function
        $configEmail = Zend_Registry::get('config')->email;

        switch (strtolower($configEmail->transport)) {
            case 'smtp':
                Zend_Mail::setDefaultTransport(
                    new Zend_Mail_Transport_Smtp(
                        $configEmail->host,
                        $configEmail->toArray()
                    )
                );
                break;
            case 'mock':
                Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Mock());
                break;
            default:
                Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Sendmail());
        }

        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyText($emailTemplate);
        $mail->setFrom($configEmail->supportemail);
        $mail->addTo($user->email);
        $mail->setSubject($subject);

        return $mail;
    }
}
