<?php

#
#  -------  ENVIRONMENT ------------
#
$config['environment']['installed']         = {environment.installed};
$config['environment']['session_name']      = '{environment.session_name}';
$config['environment']['production']        = {environment.production};
$config['environment']['YDN']               = {environment.YDN};
$config['environment']['ajax_slowdown']     = {environment.ajax_slowdown};
$config['environment']['keep_history_days'] = {environment.keep_history_days};
$config['environment']['unconfirmed_accounts_days_expire'] = {environment.unconfirmed_accounts_days_expire};

# Enable / Disable account self-registration.
$config['environment']['registrations_enabled'] = {environment.registrations_enabled};

# use auto to use the browser's language
$config['environment']['locale']            = '{environment.locale}';

$config['environment']['template']          = '{environment.template}';



#
#  -------  HTML metadata ------------
#
$config['metadata']['description']          = '{metadata.description}';
$config['metadata']['keywords']             = '{metadata.keywords}';


#
#  -------  LOGGING ------------
#
# Enter a path relative to the installation's root dir, or an absolute path.
# The file must exist, and be writable by the web server user
$config['logging']['location']              = '{logging.location}';

# Log level. You can use any of these constants or numbers:
# Zend_Log::EMERG   = 0;  // Emergency: system is unusable
# Zend_Log::ALERT   = 1;  // Alert: action must be taken immediately
# Zend_Log::CRIT    = 2;  // Critical: critical conditions
# Zend_Log::ERR     = 3;  // Error: error conditions
# Zend_Log::WARN    = 4;  // Warning: warning conditions
# Zend_Log::NOTICE  = 5;  // Notice: normal but significant condition
# Zend_Log::INFO    = 6;  // Informational: informational messages (requested URL, POST payloads)
# Zend_Log::DEBUG   = 7;  // Debug: debug messages (database queries)
$config['logging']['level']                 = {logging.level};


#
#  -------  Subdomain openid URL configuration ------------
#
# Set to true for the OpenID URL identifying the user to have the form username.hostname
# All other URLs for non-OpenID transactions will be handled under the domain name, without a subdomain.
# Take a look at the wiki for more instructions on how to set this up.
# Warning: if you change this, all current OpenId credentials will become invalid.
$config['subdomain']['enabled']             = {subdomain.enabled};
# Enter your server's hostname (without www and without an ending slash)
# Community-id must be installed directly at this hostname's root web dir
$config['subdomain']['hostname']            = '{subdomain.hostname}';
# Set to true if your regular non-OpenId URLs are prepended with www
$config['subdomain']['use_www']             = {subdomain.use_www};


#
#  -------  SSL ------------
#
# enable_mixed_mode: Set to true when you want to have the user authentication and all OpenID transactions
# to occur under SSL, and the rest to remain under a regular non-encrypted connection.
# Warning: if you change this, all current OpenId credentials will become invalid
$config['SSL']['enable_mixed_mode']         = {SSL.enable_mixed_mode};


#
#  -------  DATABASE ------------
#
$config['database']['adapter']              = '{database.adapter}';
$config['database']['params']['host']       = '{database.params.host}';
$config['database']['params']['dbname']     = '{database.params.dbname}';
$config['database']['params']['username']   = '{database.params.username}';
$config['database']['params']['password']   = '{database.params.password}';



#
#  -------  PASSWORDS ------------
#
# Point to file with a blacklist of words
# The path must relative to Community-ID's root directory.
$config['security']['passwords']['dictionary'] = '{security.passwords.dictionary}';

# If set to true, the password should not contain the username
$config['security']['passwords']['username_different'] = {security.passwords.username_different};

# Set the password's minimum length
$config['security']['passwords']['minimum_length'] = {security.passwords.minimum_length};

# Set to true if the password should contain number characters
$config['security']['passwords']['include_numbers'] = {security.passwords.include_numbers};

# Set to true if the password should contain non alpha-numeric characters
$config['security']['passwords']['include_symbols'] = {security.passwords.include_symbols};

# Set to true if the password should contain both lower case and uppercase characters
$config['security']['passwords']['lowercase_and_uppercase'] = {security.passwords.lowercase_and_uppercase};


#
#  -------  USERNAMES ------------
#
# Enter a regular expression (or litteral) for usernames you wish to exclude
# You can add as many entries as you want
$config['security']['usernames']['exclude'][0] = '{security.usernames.exclude}';


#
#  -------  LDAP ------------
#
$config['ldap']['enabled']                  = {ldap.enabled};
$config['ldap']['host']                     = '{ldap.host}';
$config['ldap']['baseDn']                   = '{ldap.baseDn}';
$config['ldap']['bindRequiresDn']           = {ldap.bindRequiresDn};

# credentials for LDAP administator user. Username must be a DN. This is not the same
# as the Community-ID administrator user.
$config['ldap']['username']                 = '{ldap.username}';
$config['ldap']['password']                 = '{ldap.password}';

# CN for the Community-ID admin
$config['ldap']['admin']                    = '{ldap.admin}';

# If set to true, when the Account Info is updated or the account is deleted,
# then the LDAP record is updated/deleted as well.
# If set to false, the account info cannot be modified.
# This doesn't apply to the Personal Info Section.
$config['ldap']['keepRecordsSynced']       = {ldap.keepRecordsSynced};

# If set to true, the user can change his password, and the LDAP record is updated as well.
$config['ldap']['canChangePassword']        = {ldap.canChangePassword};

# Hashing algorithm used to store passwords in LDAP
# If you prefer to leave the passwords unhashed, set to false.
$config['ldap']['passwordHashing']          = '{ldap.passwordHashing}';

# These defaults are drawn from an inetOrgPerson LDAP Object class
$config['ldap']['fields']['nickname']       = '{ldap.fields.nickname}';
$config['ldap']['fields']['email']          = '{ldap.fields.email}';
$config['ldap']['fields']['fullname']       = '{ldap.fields.fullname}';
$config['ldap']['fields']['postcode']       = '{ldap.fields.postcode}';


#
#  -------  YUBIKEY ------------
#
$config['yubikey']['enabled']               = {yubikey.enabled};

# Set to true to force utilization of the Yubikey, instead of passwords.
# Only use it for newer installations, as current existent users won't be able to log-in.
$config['yubikey']['force']                 = {yubikey.force};

$config['yubikey']['api_id']                = '{yubikey.api_id}';
$config['yubikey']['api_key']               = '{yubikey.api_key}';


#
#  -------  E-MAIL ------------
#
$config['email']['supportemail']            = '{email.supportemail}';

# this email will receive any error notification
$config['email']['adminemail']              = '{email.adminemail}';

$config['email']['transport']               = '{email.transport}';
$config['email']['host']                    = '{email.host}';
$config['email']['auth']                    = '{email.auth}';
$config['email']['username']                = '{email.username}';
$config['email']['password']                = '{email.password}';
