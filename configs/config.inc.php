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

/**
 * Print debugging and log to the debug file
 */
if (!defined('CORTO_DEBUG')) {
    define('CORTO_DEBUG', true);
}

/**
 * File path to a file where debugging should be appended
 */
if (!defined('CORTO_DEBUG_LOG')) {
    define('CORTO_DEBUG_LOG', '/tmp/corto_debug.log');
}

/**
 * Add traces to pages
 */
if (!defined('CORTO_TRACE')) {
    define('CORTO_TRACE', true);
}

/**
 * Where Are You From URL
 */
if (!defined('CORTO_WAYF_URL')) {
    define('CORTO_WAYF_URL', CORTO_BASE_URL . 'wayf');
}

/**
 * Path that the cookies are valid for defaults to the script name (eg: /corto.php)
 */
if (!defined('CORTO_COOKIE_PATH')) {
    define('CORTO_COOKIE_PATH', '/');
}

/**
 * Use secure cookies
 */
if (!defined('CORTO_USE_SECURE_COOKIES')) {
    define('CORTO_USE_SECURE_COOKIES', false);
}

/**
 * Signing algoritm to use, in URL form
 *
 * @example http://www.w3.org/2000/09/xmldsig#rsa-sha1
 */
if (!defined('CORTO_SIGNING_ALGORITM')) {
    define('CORTO_SIGNING_ALGORITHM', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
}

/**
 * Maximum age of a request/response in seconds
 */
if (!defined('CORTO_MAX_AGE_SECONDS')) {
    define('CORTO_MAX_AGE_SECONDS', 600);
}

/**
 * Whether or not we require user consent.
 */
if (!defined('CORTO_USE_CONSENT')) {
    define('CORTO_USE_CONSENT', true);
}

/**
 * The Database Source Name to use with PDO when connecting
 * @link http://en.wikipedia.org/wiki/Database_Source_Name
 */
if (!defined('CORTO_CONSENT_DB_DSN')) {
    define('CORTO_CONSENT_DB_DSN', 'mysql:host=localhost;dbname=engineblock');
}

/**
 * The username to connect to the database for storing consent
 */
if (!defined('CORTO_CONSENT_DB_USER')) {
    define('CORTO_CONSENT_DB_USER', 'root');
}

/**
 * The password to connect to the database for storing consent
 */
if (!defined('CORTO_CONSENT_DB_PASSWORD')) {
    define('CORTO_CONSENT_DB_PASSWORD', 'engineblock');
}

/**
 * The table to store consent in
 */
if (!defined('CORTO_CONSENT_DB_TABLE')) {
    define('CORTO_CONSENT_DB_TABLE', 'consent');
}

/**
 * Whether or not to remember the combination of attribute names WITH the values,
 * instead of just the attribute names.
 */
if (!defined('CORTO_CONSENT_STORE_VALUES')) {
    define('CORTO_CONSENT_STORE_VALUES', true);
}

if (!defined('ENGINEBLOCK_SERVICEREGISTRY_METADATA_URL')) {
    define(
        'ENGINEBLOCK_SERVICEREGISTRY_METADATA_URL',
        'https://serviceregistry.ebdev.net/simplesaml/module.php/janus/exportentities.php?state=prodaccepted&mimetype=application%2Fxml'
    );
}