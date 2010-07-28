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

/**
 * Print debugging and log to the debug file
 */
define('CORTO_DEBUG', true);

/**
 * File path to a file where debugging should be appended
 */
define('CORTO_DEBUG_LOG', '/tmp/corto_debug.log');

/**
 * Add traces to pages
 */
define('CORTO_TRACE', true);

/**
 * Where Are You From URL
 */
define('CORTO_WAYF_URL', CORTO_BASE_URL . 'wayf');

/**
 * Path that the cookies are valid for defaults to the script name (eg: /corto.php)
 */
define('CORTO_COOKIE_PATH', '/');

/**
 * Use secure cookies
 */
define('CORTO_USE_SECURE_COOKIES', false);

/**
 * Signing algoritm to use, in URL form
 *
 * @example http://www.w3.org/2000/09/xmldsig#rsa-sha1
 */
define('CORTO_SIGNING_ALGORITHM', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');

/**
 * Maximum age of a request/response in seconds
 */
define('CORTO_MAX_AGE_SECONDS', 600);