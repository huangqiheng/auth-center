<?php

#
#  -------  ENVIRONMENT ------------
#
$config['environment']['installed']         = false;
$config['environment']['session_name']      = 'COMMUNITYID';
$config['environment']['production']        = true;
$config['environment']['YDN']               = true;
$config['environment']['ajax_slowdown']     = 0;
$config['environment']['keep_history_days'] = 90;
$config['environment']['unconfirmed_accounts_days_expire'] = 0;

# Enable / Disable account self-registration.
$config['environment']['registrations_enabled'] = true;

# use auto to use the browser's language
$config['environment']['locale']            = 'auto';

$config['environment']['template']          = 'default';


#
#  -------  HTML metadata ------------
#
$config['metadata']['description']          = 'Community-ID, the open source OpenID provider';
$config['metadata']['keywords']             = 'Community-ID, OpenID, Open source';


#
#  -------  LOGGING ------------
#
# Enter a path relative to the installation's root dir, or an absolute path.
# The file must exist, and be writable by the web server user
$config['logging']['location']              = '/var/log/communityid.log';

# Log level. You can use any of these constants or numbers:
# Zend_Log::EMERG   = 0;  // Emergency: system is unusable
# Zend_Log::ALERT   = 1;  // Alert: action must be taken immediately
# Zend_Log::CRIT    = 2;  // Critical: critical conditions
# Zend_Log::ERR     = 3;  // Error: error conditions
# Zend_Log::WARN    = 4;  // Warning: warning conditions
# Zend_Log::NOTICE  = 5;  // Notice: normal but significant condition
# Zend_Log::INFO    = 6;  // Informational: informational messages (requested URL, POST payloads)
# Zend_Log::DEBUG   = 7;  // Debug: debug messages (database queries)
$config['logging']['level']                 = 0;


#
#  -------  Subdomain openid URL configuration ------------
#
# Set to true for the OpenID URL identifying the user to have the form username.hostname
# All other URLs for non-OpenID transactions will be handled under the domain name, without a subdomain.
# Take a look at the wiki for more instructions on how to set this up.
# Warning: if you change this, all current OpenId credentials will become invalid.
$config['subdomain']['enabled']             = false;
# Enter your server's hostname (without www and without an ending slash)
# Community-id must be installed directly at this hostname's root web dir
$config['subdomain']['hostname']            = '';
# Set to true if your regular non-OpenId URLs are prepended with www
$config['subdomain']['use_www']             = true;


#
#  -------  SSL ------------
#
# enable_mixed_mode: Set to true when you want to have the user authentication and all OpenID transactions
# to occur under SSL, and the rest to remain under a regular non-encrypted connection.
# Warning: if you change this, all current OpenId credentials will become invalid
$config['SSL']['enable_mixed_mode']         = false;


#
#  -------  DATABASE ------------
#
$config['database']['adapter']              = 'mysqli';
$config['database']['params']['host']       = '';
$config['database']['params']['dbname']     = 'communityid';
$config['database']['params']['username']   = '';
$config['database']['params']['password']   = '';


#
#  -------  PASSWORDS ------------
#
# Point to file with a blacklist of words
# The path must relative to Community-ID's root directory.
$config['security']['passwords']['dictionary'] = 'libs/Monkeys/Dictionaries/english.txt';

# If set to true, the password should not contain the username
$config['security']['passwords']['username_different'] = true;

# Set the password's minimum length
$config['security']['passwords']['minimum_length'] = 6;

# Set to true if the password should contain number characters
$config['security']['passwords']['include_numbers'] = true;

# Set to true if the password should contain non alpha-numeric characters
$config['security']['passwords']['include_symbols'] = true;

# Set to true if the password should contain both lower case and uppercase characters
$config['security']['passwords']['lowercase_and_uppercase'] = true;


#
#  -------  USERNAMES ------------
#
# Enter a regular expression (or litteral) for usernames you wish to exclude
# You can add as many entries as you want
$config['security']['usernames']['exclude'][0] = '';


#
#  -------  LDAP ------------
#
# Warning: Only turn on for new installations.
# Ask for help if you want to migrate from a DB-based installation to an LDAP one.
#
$config['ldap']['enabled']                  = false;
$config['ldap']['host']                     = 'localhost';
$config['ldap']['baseDn']                   = 'ou=users,dc=community-id,dc=org';
$config['ldap']['bindRequiresDn']           = true;

# credentials for LDAP administator user. Username must be a DN. This is not the same
# as the Community-ID administrator user.
$config['ldap']['username']                 = 'cn=admin,dc=community-id,dc=org';
$config['ldap']['password']                 = 'admin';

# CN for the Community-ID admin
$config['ldap']['admin']                    = 'admin';

# If set to true, when the Account Info is updated or the account is deleted,
# then the LDAP record is updated/deleted as well.
# If set to false, the account info cannot be modified.
# This doesn't apply to the Personal Info Section.
$config['ldap']['keepRecordsSynced']       = true;

# If set to true, the user can change his password, and the LDAP record is updated as well.
$config['ldap']['canChangePassword']        = true;

# Hashing algorithm used to store passwords in LDAP
# If you prefer to leave the passwords unhashed, set to false.
$config['ldap']['passwordHashing']          = 'SSHA';

# These defaults are drawn from an inetOrgPerson LDAP Object class
$config['ldap']['fields']['nickname']       = 'cn';
$config['ldap']['fields']['email']          = 'mail';
$config['ldap']['fields']['fullname']       = 'givenname+sn';
$config['ldap']['fields']['postcode']       = 'postalCode';


#
#  -------  YUBIKEY ------------
#
$config['yubikey']['enabled']               = false;

# Set to true to force utilization of the Yubikey, instead of passwords.
# Only use it for newer installations, as current existent users won't be able to log-in.
$config['yubikey']['force']                 = false;

$config['yubikey']['api_id']                = '';
$config['yubikey']['api_key']               = '';


#
#  -------  E-MAIL ------------
#
$config['email']['supportemail']            = '';

# this email will receive any error notification
$config['email']['adminemail']              = '';

$config['email']['transport']               = 'sendmail';
$config['email']['host']                    = '';
$config['email']['auth']                    = '';
$config['email']['username']                = '';
$config['email']['password']                = '';
