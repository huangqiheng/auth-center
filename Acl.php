<?php

/*
* @copyright Copyright (C) 2005-2010 Keyboard Monkeys Ltd. http://www.kb-m.com
* @license http://creativecommons.org/licenses/BSD/ BSD License
* @author Keyboard Monkey Ltd
* @since  CommunityID 0.9
* @package CommunityID
* @packager Keyboard Monkeys
*/


self::$acl->addRole(new Zend_Acl_Role(Users_Model_User::ROLE_GUEST))
          ->addRole(new Zend_Acl_Role(Users_Model_User::ROLE_REGISTERED), Users_Model_User::ROLE_GUEST)
          ->addRole(new Zend_Acl_Role(Users_Model_User::ROLE_ADMIN), Users_Model_User::ROLE_REGISTERED);

/**************************
* ACTION CONTROLLER PRIVILEGES
*
* format: $privileges[module][controller][action] = role;
**************************/
$privileges['default']['index']['index']               = Users_Model_User::ROLE_GUEST;
$privileges['default']['identity']['index']            = Users_Model_User::ROLE_GUEST;
$privileges['default']['identity']['id']               = Users_Model_User::ROLE_GUEST;

$privileges['default']['error']['error']               = Users_Model_User::ROLE_GUEST;

$privileges['default']['openid']['provider']               = Users_Model_User::ROLE_GUEST;
$privileges['default']['openid']['login']                  = Users_Model_User::ROLE_GUEST;
$privileges['default']['openid']['authenticate']           = Users_Model_User::ROLE_GUEST;
$privileges['default']['openid']['trust']                  = Users_Model_User::ROLE_REGISTERED;
$privileges['default']['openid']['proceed']                = Users_Model_User::ROLE_REGISTERED;

$privileges['default']['profile']['index']                = Users_Model_User::ROLE_REGISTERED;

$privileges['default']['sites']['index']                  = Users_Model_User::ROLE_REGISTERED;
$privileges['default']['sites']['list']                  = Users_Model_User::ROLE_REGISTERED;
$privileges['default']['sites']['deny']                  = Users_Model_User::ROLE_REGISTERED;
$privileges['default']['sites']['allow']                  = Users_Model_User::ROLE_REGISTERED;
$privileges['default']['sites']['delete']                  = Users_Model_User::ROLE_REGISTERED;

$privileges['default']['history']['index']                  = Users_Model_User::ROLE_REGISTERED;
$privileges['default']['history']['list']                   = Users_Model_User::ROLE_REGISTERED;
$privileges['default']['history']['clear']                  = Users_Model_User::ROLE_REGISTERED;

$privileges['default']['messageusers']['index']  = Users_Model_User::ROLE_ADMIN;
$privileges['default']['messageusers']['send']   = Users_Model_User::ROLE_ADMIN;

$privileges['default']['maintenancemode']['enable']    = Users_Model_User::ROLE_ADMIN;
$privileges['default']['maintenancemode']['disable']   = Users_Model_User::ROLE_ADMIN;

$privileges['default']['feedback']['index']       = Users_Model_User::ROLE_GUEST;
$privileges['default']['feedback']['send']        = Users_Model_User::ROLE_GUEST;

$privileges['default']['privacy']['index']        = Users_Model_User::ROLE_GUEST;

$privileges['default']['about']['index']          = Users_Model_User::ROLE_GUEST;

$privileges['default']['learnmore']['index']      = Users_Model_User::ROLE_GUEST;

$privileges['default']['cid']['index']            = Users_Model_User::ROLE_ADMIN;

$privileges['install']['index']['index']                = Users_Model_User::ROLE_GUEST;
$privileges['install']['permissions']['index']          = Users_Model_User::ROLE_GUEST;
$privileges['install']['credentials']['index']          = Users_Model_User::ROLE_GUEST;
$privileges['install']['credentials']['save']           = Users_Model_User::ROLE_GUEST;
$privileges['install']['complete']['index']             = Users_Model_User::ROLE_GUEST;
$privileges['install']['upgrade']['index']              = Users_Model_User::ROLE_GUEST;
$privileges['install']['upgrade']['proceed']            = Users_Model_User::ROLE_GUEST;

$privileges['users']['login']['index']            = Users_Model_User::ROLE_GUEST;
$privileges['users']['login']['logout']           = Users_Model_User::ROLE_GUEST;
$privileges['users']['login']['authenticate']     = Users_Model_User::ROLE_GUEST;

$privileges['users']['userlist']['index']       = Users_Model_User::ROLE_ADMIN;

$privileges['users']['register']['index']         = Users_Model_User::ROLE_GUEST;
$privileges['users']['register']['save']          = Users_Model_User::ROLE_GUEST;
$privileges['users']['register']['eula']          = Users_Model_User::ROLE_GUEST;
$privileges['users']['register']['declineeula']   = Users_Model_User::ROLE_GUEST;
$privileges['users']['register']['accepteula']    = Users_Model_User::ROLE_GUEST;

$privileges['users']['profile']['index']          = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['profile']['edit']           = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['profile']['save']           = Users_Model_User::ROLE_REGISTERED;

$privileges['users']['personalinfo']['index']           = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['personalinfo']['edit']           = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['personalinfo']['save']           = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['personalinfo']['delete']         = Users_Model_User::ROLE_REGISTERED;

$privileges['users']['profilegeneral']['accountinfo']     = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['editaccountinfo']     = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['saveaccountinfo']     = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['changepassword']     = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['savepassword']     = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['confirmdelete']     = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['profilegeneral']['delete']            = Users_Model_User::ROLE_REGISTERED;

$privileges['users']['recoverpassword']['index']  = Users_Model_User::ROLE_GUEST;
$privileges['users']['recoverpassword']['send']  = Users_Model_User::ROLE_GUEST;
$privileges['users']['recoverpassword']['reset']  = Users_Model_User::ROLE_GUEST;

$privileges['users']['manageusers']['index']  = Users_Model_User::ROLE_ADMIN;
$privileges['users']['manageusers']['delete']  = Users_Model_User::ROLE_ADMIN;
$privileges['users']['manageusers']['deleteunconfirmed']  = Users_Model_User::ROLE_ADMIN;
$privileges['users']['manageusers']['sendreminder']  = Users_Model_User::ROLE_ADMIN;

$privileges['users']['userslist']['index']  = Users_Model_User::ROLE_ADMIN;

$privileges['users']['signinimage']['index']  = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['signinimage']['saveimage']  = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['signinimage']['setcookie']  = Users_Model_User::ROLE_REGISTERED;
$privileges['users']['signinimage']['image']  = Users_Model_User::ROLE_GUEST;


$privileges['stats']['index']['index']          = Users_Model_User::ROLE_ADMIN;
$privileges['stats']['reports']['index']        = Users_Model_User::ROLE_ADMIN;
$privileges['stats']['reports']['graph']        = Users_Model_User::ROLE_ADMIN;

$privileges['news']['index']['index']           = Users_Model_User::ROLE_GUEST;
$privileges['news']['view']['index']            = Users_Model_User::ROLE_GUEST;
$privileges['news']['edit']['add']              = Users_Model_User::ROLE_ADMIN;
$privileges['news']['edit']['index']            = Users_Model_User::ROLE_ADMIN;
$privileges['news']['edit']['save']             = Users_Model_User::ROLE_ADMIN;
$privileges['news']['edit']['delete']           = Users_Model_User::ROLE_ADMIN;
