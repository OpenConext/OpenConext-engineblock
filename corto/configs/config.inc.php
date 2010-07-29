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

error_reporting(E_ALL);
ini_set('display_errors', true);

/**
 * Print debugging and log to the debug file
 */
define('CORTO_DEBUG', false);

/**
 * File path to a file where debugging should be appended
 */
define('CORTO_DEBUG_LOG', '/tmp/corto_debug.log');

/**
 * Add traces to pages
 */
define('CORTO_TRACE', false);

/**
 * Where Are You From URL
 */
define('CORTO_WAYF_URL', CORTO_BASE_URL . 'wayf');

/**
 * Path that the cookies are valid for defaults to the script name (eg: /corto.php)
 */
define('CORTO_COOKIE_PATH', $_SERVER['SCRIPT_NAME']);

/**
 * Use secure cookies
 */
define('CORTO_USE_SECURE_COOKIES', true);

/**
 * Signing algoritm to use, in URL form
 *
 * @example http://www.w3.org/2000/09/xmldsig#rsa-sha1
 */
define('CORTO_SIGNING_ALGORITHM', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');

/**
 * Maximum age of a request/response in seconds
 */
define('CORTO_MAX_AGE_SECONDS', 60);

/**
 * Whether or not we require user consent.
 */
define('CORTO_USE_CONSENT', false);

/**
 * The Database Source Name to use with PDO when connecting
 * @link http://en.wikipedia.org/wiki/Database_Source_Name
 */
define('CORTO_CONSENT_DB_DSN', '');

/**
 * The username to connect to the database for storing consent
 */
define('CORTO_CONSENT_DB_USER', '');

/**
 * The password to connect to the database for storing consent
 */
define('CORTO_CONSENT_DB_PASSWORD', '');

/**
 * The table to store consent in
 */
define('CORTO_CONSENT_DB_TABLE', 'consent');

/**
 * Whether or not to remember the combination of attribute names WITH the values,
 * instead of just the attribute names.
 */
define('CORTO_CONSENT_STORE_VALUES', true);