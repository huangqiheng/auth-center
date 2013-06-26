<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Install_UpgradeController extends CommunityID_Controller_Action
{
    protected $_numCols = 1;

    public function indexAction()
    {
        // double check upgrade is necessary in case someone access this action directly
        if (!$this->_needsUpgrade()) {
            $this->_redirect('');
            return;
        }

        $appSession = Zend_Registry::get('appSession');
        if (isset($appSession->loginForm)) {
            $this->view->loginForm = $appSession->loginForm;
            unset($appSession->loginForm);
        } else {
            $this->view->loginForm = new Install_Form_UpgradeLogin();
        }
    }

    public function proceedAction()
    {
        // double check upgrade is necessary in case someone access this action directly
        if (!$this->_needsUpgrade()) {
            $this->_redirect('');
            return;
        }

        $form = new Install_Form_UpgradeLogin();
        $formData = $this->_request->getPost();
        $form->populate($formData);

        if (!$form->isValid($formData)) {
            $appSession = Zend_Registry::get('appSession');
            $appSession->loginForm = $form;
            $this->_forward('index');
            return;
        }

        $users = new Users_Model_Users();
        list($super, $mayor, $minor) = explode('.', $this->_getDbVersion());
        $greaterThan2 = $super >= 2;
        $result = $users->authenticate(
            $this->_request->getPost('username'),
            $this->_request->getPost('password'),
            false,
            $this->view,
            !$greaterThan2 // bypass mark successfull login 'cause last_login field only exists after v.2
            );

        if (!$result) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Invalid credentials'));
            $this->_redirect('index');
            return;
        }

        $user = $users->getUser();
        if ($user->role != Users_Model_User::ROLE_ADMIN) {
            Zend_Auth::getInstance()->clearIdentity();
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Invalid credentials'));
            $this->_redirect('index');
            return;
        }

        $this->_runUpgrades(true);
        $upgradedVersion = $this->_runUpgrades(false);

        $this->_helper->FlashMessenger->addMessage($this->view->translate('Upgrade was successful. You are now on version %s', $upgradedVersion));

        $missingConfigs = $this->_checkMissingConfigDirectives();
        if ($missingConfigs) {
            $this->_helper->FlashMessenger->addMessage($this->view->translate('WARNING: there are some new configuration settings. To override their default values (as set in config.default.php) add them to your config.php file. The new settings correspond to the following directives: %s.', implode(', ', $missingConfigs)));
        }

        // we need to logout user in case the user table changed
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::forgetMe();

        $this->_redirect('/');
    }

    private function _runUpgrades($onlyCheckFiles = true)
    {
        require 'setup/versions.php';

        $includeFiles = false;
        $db = Zend_Registry::get('db');
        $errors = array();
        foreach ($versions as $version) {
            if ($version == $this->_getDbVersion()) {
                $includeFiles = true;
                continue;
            }

            if (!$includeFiles) {
                continue;
            }

            $sqlFileName = APP_DIR . '/setup/upgrade_'.$version.'.sql';
            $phpFileName = APP_DIR . '/setup/upgrade_'.$version.'.php';
            $className = 'Upgrade_' . strtr($version, '.', '_');

            if ($onlyCheckFiles) {
                if (!file_exists($sqlFileName)) {
                    $this->_helper->FlashMessenger->addMessage($this->view->translate('Correct before upgrading: File %s is required to proceed', $sqlFileName));
                    $this->_redirect('index');
                    return;
                }

                if (file_exists($phpFileName)) {
                    require_once $phpFileName;
                    $upgradeStage = new $className($this->user, $db, $this->view);
                    $errors = array_merge($errors, $upgradeStage->requirements());
                }

                continue;
            }

            $query = '';
            $lines = file($sqlFileName);
            Zend_Registry::get('logger')->log("Running upgrade file $sqlFileName", Zend_Log::DEBUG);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line != '') {
                    $query .= $line;
                }
                if (substr($line, -1) == ';') {
                    try {
                        $db->query($query);
                    } catch (Zend_Db_Statement_Mysqli_Exception $e) {
                        Zend_Registry::get('logger')->log("Error in this query: $query", Zend_Log::ERR);
                        throw $e;
                    }
                    $query = '';
                }
            }

            if (file_exists($phpFileName)) {
                Zend_Registry::get('logger')->log("Running upgrade file $phpFileName", Zend_Log::DEBUG);
                $upgradeStage = new $className($this->user, $db, $this->view);
                $upgradeStage->proceed();
            }
        }

        if ($errors) {
            $errorMessages = join('<br />', $errors);
            $this->_helper->FlashMessenger->addMessage($this->view->translate('Please address the following requirements before proceeding with the upgrade:') . '<br />' . $errorMessages);
            $this->_redirect('index');
        }

        return $version;
    }

    private function _checkMissingConfigDirectives()
    {
        require 'config.default.php';
        $defaultConfig = $config;
        unset($config);
        require 'config.php';
        $missingConfigs = $this->_getMissingConfigs($defaultConfig, $config);
        return $missingConfigs;
    }

    private function _getMissingConfigs($defaultConfig, $config, $baseKey = false)
    {
        $missingConfigs = array();

        foreach ($defaultConfig as $key => $value) {
            if (!isset($config[$key])) {
                $missingConfigs[] = $key;
            } else if (is_array($value)) {
                if ($this->_getMissingConfigs($defaultConfig[$key], $config[$key], $baseKey)) {
                    $missingConfigs[] = $baseKey? $baseKey : $key;
                }
            }
        }

        return $missingConfigs;
    }
}
