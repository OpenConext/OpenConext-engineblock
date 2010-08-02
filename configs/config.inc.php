<?php

/**
 * Corto default configuration file
 *
 * @package    Corto
 * @module     Configuration
 * @author     Mads Freek Petersen, <freek@ruc.dk>
 * @author     Boy Baukema, <boy@ibuildings.com>
 * @licence    MIT License, see http://www.opensource.org/licenses/mit-license.php
 * @copyright  2009-2010 WAYF.dk
 * @version    $Id:$
 */

error_reporting(E_ALL^E_NOTICE);
ini_set('display_errors', true);

$localConfigFile = dirname(__FILE__) . 'config.local.inc.php';
if (file_exists($localConfigFile)) {
    require $localConfigFile;
}

if (!defined('CORTO_DEBUG')) {
    /**
     * Print debugging and log to the debug file
     */
    define('CORTO_DEBUG', true);
}

if (!defined('CORTO_DEBUG_LOG')) {
    /**
     * File path to a file where debugging should be appended
     */
    define('CORTO_DEBUG_LOG', '/tmp/corto_debug.log');
}

if (!defined('CORTO_TRACE')) {
    /**
     * Add traces to pages
     */
    define('CORTO_TRACE', true);
}

if (!defined('CORTO_WAYF_URL')) {
    /**
     * Where Are You From URL
     */
    define('CORTO_WAYF_URL', CORTO_BASE_URL . 'wayf');
}

if (!defined('CORTO_COOKIE_PATH')) {
    /**
     * Path that the cookies are valid for defaults to the script name (eg: /corto.php)
     */
    define('CORTO_COOKIE_PATH', '/');
}

if (!defined('CORTO_USE_SECURE_COOKIES')) {
    /**
     * Use secure cookies
     */
    define('CORTO_USE_SECURE_COOKIES', false);
}

if (!defined('CORTO_SIGNING_ALGORITM')) {
    /**
     * Signing algoritm to use, in URL form
     *
     * @example http://www.w3.org/2000/09/xmldsig#rsa-sha1
     */
    define('CORTO_SIGNING_ALGORITHM', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
}

if (!defined('CORTO_MAX_AGE_SECONDS')) {
    /**
     * Maximum age of a request/response in seconds
     */
    define('CORTO_MAX_AGE_SECONDS', 600);
}

if (!defined('CORTO_MAX_AGE_SECONDS')) {
    /**
     * Maximum number of proxies allowed for a request created by Corto
     */
    define('CORTO_MAX_AGE_SECONDS', 3);
}

if (!defined('CORTO_USE_CONSENT')) {
    /**
     * Whether or not we require user consent.
     */
    define('CORTO_USE_CONSENT', true);
}

if (!defined('CORTO_CONSENT_DB_DSN')) {
    /**
     * The Database Source Name to use with PDO when connecting
     * @link http://en.wikipedia.org/wiki/Database_Source_Name
     */
    define('CORTO_CONSENT_DB_DSN', 'mysql:host=localhost;dbname=engineblock');
}

if (!defined('CORTO_CONSENT_DB_USER')) {
    /**
     * The username to connect to the database for storing consent
     */
    define('CORTO_CONSENT_DB_USER', 'root');
}

if (!defined('CORTO_CONSENT_DB_PASSWORD')) {
    /**
     * The password to connect to the database for storing consent
     */
    define('CORTO_CONSENT_DB_PASSWORD', 'engineblock');
}

if (!defined('CORTO_CONSENT_DB_TABLE')) {
    /**
     * The table to store consent in
     */
    define('CORTO_CONSENT_DB_TABLE', 'consent');
}


if (!defined('CORTO_CONSENT_STORE_VALUES')) {
    /**
     * Whether or not to remember the combination of attribute names WITH the values,
     * instead of just the attribute names.
     */
    define('CORTO_CONSENT_STORE_VALUES', true);
}

if (!defined('ENGINEBLOCK_USER_DB_DSN')) {
    define('ENGINEBLOCK_USER_DB_DSN', CORTO_CONSENT_DB_DSN);
}

if (!defined('ENGINEBLOCK_USER_DB_USER')) {
    define('ENGINEBLOCK_USER_DB_USER', CORTO_CONSENT_DB_USER);
}

if (!defined('ENGINEBLOCK_USER_DB_PASSWORD')) {
    define('ENGINEBLOCK_USER_DB_PASSWORD', CORTO_CONSENT_DB_PASSWORD);
}

if (!defined('ENGINEBLOCK_USER_DB_TABLE')) {
    define('ENGINEBLOCK_USER_DB_TABLE', 'users');
}

if (!defined('ENGINEBLOCK_SERVICEREGISTRY_METADATA_URL')) {
    define(
        'ENGINEBLOCK_SERVICEREGISTRY_METADATA_URL',
        'https://serviceregistry.ebdev.net/simplesaml/module.php/janus/exportentities.php?state=prodaccepted&mimetype=application%2Fxml'
    );
}