<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

class Application
{
    const VERSION = '2.0.0.RC3';

    public static $config;
    public static $logger;
    public static $mockLogger;
    public static $acl;
    public static $front;

    private static $_pathList;

    /**
    * Used in unit tests
    */
    public static function cleanUp()
    {
        Zend_Registry::_unsetInstance();
        Zend_Layout::resetMvcInstance();
        Zend_Controller_Action_HelperBroker::resetHelpers();
    }

    public static function setIncludePath()
    {
        if (isset(self::$_pathList)) {
            // to avoid passing here more than once in unit tests
            return;
        }

        self::$_pathList = array(
            '.',
            APP_DIR,
            APP_DIR.'/libs',
            // this should go at the end to avoid clashes with other Zend Framework versions in the machine
            get_include_path(),
        );
        if (!set_include_path(implode(PATH_SEPARATOR, self::$_pathList))) {
            die('ERROR: couldn\'t execute PHP\'s set_include_path() function in your system.'
                .' Please ask your system admin to enable that functionality.');
        }
    }

    public static function setAutoLoader()
    {
        require_once 'Zend/Loader/Autoloader.php';
        $loader = Zend_Loader_Autoloader::getInstance();
        $loader->registerNamespace('Monkeys_');
        $loader->registerNamespace('CommunityID');
        $loader->registerNamespace('Auth');
        $loader->registerNamespace('Yubico');
        new Monkeys_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath'  => APP_DIR . '/modules/default',
        ));
        new Monkeys_Application_Module_Autoloader(array(
            'namespace' => 'Users',
            'basePath'  => APP_DIR . '/modules/users',
        ));
        new Monkeys_Application_Module_Autoloader(array(
            'namespace' => 'News',
            'basePath'  => APP_DIR . '/modules/news',
        ));
        new Monkeys_Application_Module_Autoloader(array(
            'namespace' => 'Stats',
            'basePath'  => APP_DIR . '/modules/stats',
        ));
        new Monkeys_Application_Module_Autoloader(array(
            'namespace' => 'Install',
            'basePath'  => APP_DIR . '/modules/install',
        ));
    }

    public static function setConfig()
    {
        $config = array();

        // first defaults are loaded, then the custom configs
        require APP_DIR . DIRECTORY_SEPARATOR . 'config.default.php';
        if (file_exists(APP_DIR . DIRECTORY_SEPARATOR . 'config.php')) {
            require APP_DIR . DIRECTORY_SEPARATOR . 'config.php';
        }

        self::$config = new Zend_Config($config, array('allowModifications' => true));

        Zend_Registry::set('config', self::$config);
    }

    public static function setErrorReporting()
    {
        ini_set('log_errors', 'Off');
        if (self::$config->environment->production) {
            error_reporting(E_ALL & E_NOTICE);
            ini_set('display_errors', 'Off');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        }
    }

    public static function setLogger($addMockWriter = false)
    {
        self::$logger = new Zend_Log();
        if (self::$config->logging->level == 0) {
            self::$logger->addWriter(new Zend_Log_Writer_Null(APP_DIR . '/log.txt'));
        } else {
            if (is_writable(self::$config->logging->location)) {
                $file = self::$config->logging->location;
            } else if (!is_writable(APP_DIR . DIRECTORY_SEPARATOR . self::$config->logging->location)) {
                throw new Exception('Couldn\'t find log file, or maybe it\'s not writable');
            } else {
                $file = APP_DIR . DIRECTORY_SEPARATOR . self::$config->logging->location;
            }

            self::$logger->addWriter(new Zend_Log_Writer_Stream($file));
            if ($addMockWriter) {
                self::$mockLogger = new Zend_Log_Writer_Mock();
                self::$logger->addWriter(self::$mockLogger);
            }
        }
        self::$logger->addFilter(new Zend_Log_Filter_Priority((int)self::$config->logging->level));
        Zend_Registry::set('logger', self::$logger);
    }

    public static function logRequest()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            self::$logger->log('REQUESTED URI: ' . $_SERVER['REQUEST_URI'], Zend_Log::INFO);
        } else {
            self::$logger->log('REQUESTED THROUGH CLI: ' . $GLOBALS['argv'][0], Zend_Log::INFO);
        }

        if (isset($_POST) && $_POST) {
            self::$logger->log('POST payload: ' . print_r($_POST, 1), Zend_Log::INFO);
        }
    }

    public static function setDatabase()
    {
        // I was using this for when using PDO, but lately it's generating a segfault, and we're not using PDO anymore anyway
        /*if (defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) {
            // constant not set if pdo_mysql extension is not loaded
            self::$config->database->params->driver_options = array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true);
        }*/

        $db = Zend_Db::factory(self::$config->database);
        if (self::$config->logging->level == Zend_Log::DEBUG) {
            $profiler = new Monkeys_Db_Profiler();
            $db->setProfiler($profiler);
        }
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        // unknown PHP bug (tested on PHP 5.2.8 and PHP 5.2.10) corrupts the $db reference, so I gotta retrieve it again:
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        Zend_Registry::set('db', $db);

        try {
            $db->getConnection();
            return true;
        } catch (Zend_Db_Adapter_Exception $e) {
            return false;
        }
    }

    public static function setSession()
    {
        // The framework doesn't provide yet a clean way of doing this
        if (isset($_POST['rememberme'])) {
            Zend_Session::rememberMe();
        }

        // ZF still doesn't have facilities for session_name().
        session_name(self::$config->environment->session_name);

        $appSession = new Zend_Session_Namespace('Default');
        if (is_null($appSession->messages)) {
            $appSession->messages = array();
        }
        Zend_Registry::set('appSession', $appSession);
    }

    public static function setAcl()
    {
        self::$acl = new Zend_Acl();
        require 'Acl.php';

        foreach ($privileges as $module => $moduleConfig) {
            foreach ($moduleConfig as $controller => $controllerConfig) {
                self::$acl->add(new Zend_Acl_Resource($module . '_' . $controller));
                foreach ($controllerConfig as $action => $role) {
                    self::$acl->allow($role, $module . '_' . $controller, $action);
                }
            }
        }
        Zend_Registry::set('acl', self::$acl);
    }

    public static function setI18N()
    {
        if (self::$config->environment->locale == 'auto') {
            try {
                $locale = new Zend_Locale(Zend_Locale::BROWSER);
            } catch (Zend_Locale_Exception $e) {
                // happens when no browser around, e.g. when calling the rest api by other means
                $locale = new Zend_Locale('en_US');
            }
        } else {
            $locale = new Zend_Locale(self::$config->environment->locale);
        }
        Zend_Registry::set('Zend_Locale', $locale);
        $translate = new Zend_Translate('gettext',
                                        APP_DIR . '/languages',
                                        $locale->toString(),
                                        array(
                                            'scan'              => Zend_Translate::LOCALE_DIRECTORY,
                                            'disableNotices'    => true));
        Zend_Registry::set('Zend_Translate', $translate);

        return $translate;
    }

    public static function setLayout()
    {
        $template = self::$config->environment->template;

        // Hack: Explicitly add the ViewRenderer, so that when an exception is thrown,
        // the layout is not shown (should be better handled in ZF 1.6)
        // @see http://framework.zend.com/issues/browse/ZF-2993?focusedCommentId=23121#action_23121
        Zend_Controller_Action_HelperBroker::addHelper(new Zend_Controller_Action_Helper_ViewRenderer());

        Zend_Layout::startMvc(array(
            'layoutPath'    => $template == 'default'? APP_DIR.'/views/layouts' : APP_DIR."/views/layouts_$template",
        ));
    }

    public static function setFrontController()
    {
        self::$front = Zend_Controller_Front::getInstance();
        self::$front->registerPlugin(new Monkeys_Controller_Plugin_Auth(self::$acl));
        self::$front->addModuleDirectory(APP_DIR.'/modules');

        $router = self::$front->getRouter();

        if (self::$config->subdomain->enabled) {
            if (self::$config->subdomain->use_www) {
                $reqs = array('username' => '([^w]|w[^w][^w]|ww[^w]|www.+).*');
            } else {
                $reqs = array();
            }
            $hostNameRoute = new Zend_Controller_Router_Route_Hostname(
                ':username.' . self::$config->subdomain->hostname,
                array(
                    'module'        => 'default',
                    'controller'    => 'identity',
                    'action'        => 'id',
                ),
                $reqs
            );
            $router->addRoute('hostNameRoute', $hostNameRoute);
        }

        $route = new Zend_Controller_Router_Route(
            'identity/:userid',
            array(
                'module'        => 'default', 
                'controller'    => 'identity', 
                'action'        => 'id', 
            ),
            array('userid' => '[\w-]*')
        );
        $router->addRoute('identityRoute', $route);

        $route = new Zend_Controller_Router_Route(
            'news/:id',
            array(
                'module'        => 'news', 
                'controller'    => 'view', 
                'action'        => 'index', 
            ),
            array('id' => '\d+')
        );
        $router->addRoute('articleView', $route);
    }

    public static function dispatch()
    {
        self::$front->dispatch();
    }
}

/**
* this is just a global function used to mark translations
*/
function translate() {}
