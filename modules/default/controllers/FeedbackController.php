<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class FeedbackController extends CommunityID_Controller_Action
{
    protected $_numCols = 1;

    public function init()
    {
        parent::init();

        if ($this->user->role != Users_Model_User::ROLE_ADMIN && $this->underMaintenance) {
            return $this->_redirectForMaintenance();
        }
    }

    public function indexAction()
    {
        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->feedbackForm)) {
            $form = $appSession->feedbackForm;
            unset($appSession->feedbackForm);
        } else {
            $form = new Form_Feedback(null, $this->view->base);
        }
        $this->view->form = $form;
    }

    public function sendAction()
    {
        $form = new Form_Feedback(null, $this->view->base);
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->feedbackForm = $form;
            return $this->_forward('index', null, null);
        }

        $mail = self::getMail(
            $form->getValue('name'),
            $form->getValue('email'),
            $form->getValue('feedback')
        );

        try {
            $mail->send();
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Thank you for your interest. Your message has been routed.'));
        } catch (Zend_Mail_Protocol_Exception $e) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Sorry, the feedback couldn\'t be delivered. Please try again later.'));
            if ($this->_config->logging->level == Zend_Log::DEBUG) {
                $this->_helper->FlashMessenger->addMessage($e->getMessage());
            }
        }

        $this->_redirect('');
    }

    /**
    * @return Zend_Mail
    * @throws Zend_Mail_Protocol_Exception
    */
    public static function getMail($name, $email, $feedback)
    {
        // can't use $this-_config 'cause it's a static function
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
        $mail->setBodyText(<<<EOD
Dear Administrator,

The community-id feedback form has just been used to send you the following:

Name: $name
E-mail: $email
Feedback:
$feedback
EOD
);
        $mail->setFrom($configEmail->supportemail);
        $mail->addTo($configEmail->supportemail);
        $mail->setSubject('Community-ID feedback form');

        return $mail;
    }
}
