<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/

define('APP_DIR', realpath(dirname(__FILE__) . '/..'));
require APP_DIR . '/Application.php';

Application::setIncludePath();
Application::setAutoLoader();
Application::setConfig();
Application::setErrorReporting();
Application::setLogger();
$translate = Application::setI18N();

?>

YAHOO.namespace("commid");
COMMID = YAHOO.commid;

// WARNING: DO NOT PUT A COMMA AFTER THE LAST ELEMENT (breaks IE)

COMMID.lang = {
    "Name": "<?php echo $translate->translate('Name') ?>",
    "Registration": "<?php echo $translate->translate('Registration') ?>",
    "Status": "<?php echo $translate->translate('Status') ?>",
    "profile": "<?php echo $translate->translate('profile') ?>",
    "delete": "<?php echo $translate->translate('delete') ?>",
    "Site": "<?php echo $translate->translate('Site') ?>",
    "view info exchanged": "<?php echo $translate->translate('view info exchanged') ?>",
    "deny": "<?php echo $translate->translate('deny') ?>",
    "allow": "<?php echo $translate->translate('allow') ?>",
    "Are you sure you wish to send this message to ALL users?": "<?php echo $translate->translate('Are you sure you wish to send this message to ALL users?') ?>",
    "Are you sure you wish to deny trust to this site?": "<?php echo $translate->translate('Are you sure you wish to deny trust to this site?') ?>",
    "operation failed": "<?php echo $translate->translate('operation failed') ?>",
    "Trust to the following site has been granted:": "<?php echo $translate->translate('Trust to the following site has been granted:') ?>",
    "Trust the following site has been denied:": "<?php echo $translate->translate('Trust the following site has been denied:') ?>",
    "ERROR. The server returned:": "<?php echo $translate->translate('ERROR. The server returned:') ?>",
    "Your relationship with the following site has been deleted:": "<?php echo $translate->translate('Your relationship with the following site has been deleted:') ?>",
    "The history log has been cleared": "<?php echo $translate->translate('The history log has been cleared') ?>",
    "Are you sure you wish to allow access to this site?": "<?php echo $translate->translate('Are you sure you wish to allow access to this site?') ?>",
    "Are you sure you wish to delete your relationship with this site?": "<?php echo $translate->translate('Are you sure you wish to delete your relationship with this site?') ?>",
    "Are you sure you wish to delete all the History Log?": "<?php echo $translate->translate('Are you sure you wish to delete all the History Log?') ?>",
    "Are you sure you wish to delete the user": "<?php echo $translate->translate('Are you sure you wish to delete the user') ?>",
    "Are you sure you wish to delete all the unconfirmed accounts?": "<?php echo $translate->translate('Are you sure you wish to delete all the unconfirmed accounts?') ?>",
    "Date": "<?php echo $translate->translate('Date') ?>",
    "Result": "<?php echo $translate->translate('Result') ?>",
    "No records found.": "<?php echo $translate->translate('No records found.') ?>",
    "Loading...": "<?php echo $translate->translate('Loading...') ?>",
    "Data error.": "<?php echo $translate->translate('Data error.') ?>",
    "Click to sort ascending": "<?php echo $translate->translate('Click to sort ascending') ?>",
    "Click to sort descending": "<?php echo $translate->translate('Click to sort descending') ?>",
    "Authorized": "<?php echo $translate->translate('Authorized') ?>",
    "Denied": "<?php echo $translate->translate('Denied') ?>",
    "of": "<?php echo $translate->translate('of') ?>",
    "next": "<?php echo $translate->translate('next') ?>",
    "prev": "<?php echo $translate->translate('prev') ?>",
    "IP": "<?php echo $translate->translate('IP') ?>",
    "Delete unconfirmed accounts older than how many days?": "<?php echo $translate->translate('Delete unconfirmed accounts older than how many days?') ?>",
    "The value entered is incorrect": "<?php echo $translate->translate('The value entered is incorrect') ?>",
    "Send reminder to accounts older than how many days?": "<?php echo $translate->translate('Send reminder to accounts older than how many days?') ?>",
    "Are you sure you wish to delete this article?": "<?php echo $translate->translate('Are you sure you wish to delete this article?') ?>",
    "reminder": "<?php echo $translate->translate('reminder') ?>",
    "reminders": "<?php echo $translate->translate('reminders') ?>",
    "Are you sure you wish to delete this profile?": "<?php echo $translate->translate('Are you sure you wish to delete this profile?') ?>"
}
