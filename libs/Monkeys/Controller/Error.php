<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD Licensese
* @author Keyboard Monkeys Ltd.
* @package Monkeys Framework
* @packager Keyboard Monkeys
*/

abstract class Monkeys_Controller_Error extends Monkeys_Controller_Action
{
    protected $_numCols = 1;

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$this->_config->environment->production) {
            echo "<br /><br />";
            Zend_Debug::Dump($errors);
        }

        $exceptionClass = get_class($errors->exception);

        Zend_Registry::get('logger')->log(
            "Exception $exceptionClass\nMessage: ".$errors->exception->getMessage()."\nStack: \n" . print_r($errors->exception->getTraceAsString(), true),
            Zend_Log::ERR
        );

        switch ($exceptionClass) {
            case 'Monkeys_BadUrlException';
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');

                $this->view->message = $this->_getTranslationForException($exceptionClass);
                break;
            case 'Monkeys_AccessDeniedException';
                $this->getResponse()->setRawHeader('HTTP/1.1 401 Unauthorized');
                $this->view->message = $this->_getTranslationForException($exceptionClass);
                break;
            default:
                $this->view->message = get_class($errors->exception) . '<br />' . $errors->exception->getMessage();
                if (!$this->_config->environment->production) {
                    $this->view->trace = $errors->exception->getTraceAsString();
                } else if ($this->_config->email->adminemail) {
                    $mail = self::getMail($errors->exception, $this->user, $errors);
                    $mail->send();
                    $this->view->message .= "<br />\n";
                    $this->view->message .= 'The system administrator has been notified.';
                }
                break;
        }

        $this->getResponse()->clearBody();
    }

    /**
    * @return Zend_Mail
    * @throws Zend_Mail_Protocol_Exception
    */
    public static function getMail(Exception $ex, User $user, $errors)
    {
        $exceptionClass = get_class($ex);
        $stack = $ex->getTraceAsString();
        $stackDetail = print_r($errors, true);
        $currentUrl = Zend_OpenId::selfURL();
        if ($user->role = ROLE_GUEST) {
            $userLabel = 'Anonymous';
        } else {
            $userLabel = $user->getFullName() . '(' . $user->username . ')';
        }

        $body = <<<EOD
Dear Admin,

An error has occured in your Community-ID installation.

URL requested: $currentUrl

By User: $userLabel

Exception: $exceptionClass

Call stack:
$stack

Call stack detail:
$stackDetail
EOD;

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

        $mail = new Zend_Mail();
        $mail->setBodyText($body);
        $mail->setFrom($this->_config->email->supportemail);
        $mail->addTo($configEmail->adminemail);
        $mail->setSubject('Community-ID error report');

        return $mail;
    }

    protected function _validateTargetUser()
    {
    }

    /**
    * Returns translation for an exception message
    *
    * Override using your translation engine.
    */
    protected function _getTranslationForException($ex)
    {
        switch ($ex) {
            case 'Monkeys_BadUrlException':
                return 'The URL you entered is incorrect. Please correct and try again.';
                break;
            case 'Monkeys_AccessDeniedException':
                return 'Access Denied - Maybe your session has expired? Try logging-in again.';
                break;
            default:
                return $ex;
        }
    }
}
