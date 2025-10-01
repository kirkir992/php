<?php
// config.php - Active Directory Configuration
session_start();

// AD Configuration
define('AD_SERVER', '192.168.0.165');
define('AD_DOMAIN', 'htz.lan');
define('AD_BASEDN', 'dc=htz,dc=lan');
define('AD_PORT', 389);

// Application settings
define('APP_NAME', 'Company Portal');
define('LOGIN_TIMEOUT', 1800); // 30 minutes

// Groups that are allowed to access the application
$allowed_groups = array(
    'test',
    'zendto'
);

// LDAP Connection settings
$ldap_config = array(
    'host' => AD_SERVER,
    'port' => AD_PORT,
    'use_tls' => false,
    'base_dn' => AD_BASEDN,
    'domain' => AD_DOMAIN
);
?>
