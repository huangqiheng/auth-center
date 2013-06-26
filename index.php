<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

define('APP_DIR', dirname(__FILE__));

// change this if separating app code from web-accessible files
define('WEB_DIR', APP_DIR);

require 'Application.php';

Application::setIncludePath();
Application::setAutoLoader();
Application::setConfig();
Application::setErrorReporting();
Application::setLogger();
Application::logRequest();
Application::setDatabase();
Application::setSession();
Application::setAcl();
Application::setI18N();
Application::setLayout();
Application::setFrontController();
Application::dispatch();
