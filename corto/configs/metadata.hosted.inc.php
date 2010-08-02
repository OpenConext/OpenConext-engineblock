<?php

/**
 * Corto default metadata file
 *
 * @package    Corto
 * @module     Configuration
 * @author     Mads Freek Petersen, <freek@ruc.dk>
 * @author     Boy Baukema, <boy@ibuildings.com>
 * @licence    MIT License, see http://www.opensource.org/licenses/mit-license.php
 * @copyright  2009-2010 WAYF.dk
 * @version    $Id:$
 */

/**
 * = hosted
 * Array with servers hosted by Cortis, each server has the URL it is hosted on as array key.
 * example:
 * $GLOBALS['metabase'] = array('hosted'=> array('http://localhost/cortis.php/main'=>...));
 *
 * Note that the servername (or entitycode for hosted entity) MUST NOT contain an _ as this is used
 * to specify pre-selecting an IdP like so:
 * http://localhost/cortis.php/main_myidp/SingleSignOnService
 * (don't show a discovery/Where Are You From screen,
 *  but just use the remote http://localhost/cortis.php/myidp/SingleSignOnService)
 *
 * As value it can have an array with one or more of the following configuration options:
 *
 * == infilter
 * Callback for
 *
 * == outfilter
 *
 * == keepsession
 * Cache the assertion in the session, so every new request to the same IdP for that session will simply reuse the old
 * assertion.
 *
 * == IDPList
 *
 * == idp
 * Use only this IdP
 *
 * == key
 * Key file (not implemented yet)
 *
 * == virtual
 * Use 'Virtual IdP' feature, use this to list multiple IdPs and make Corto
 *
 * When Corto is using the hosted server, it will add the following properties:
 *
 * == entityID
 * The full URL of the entity (http://localhost/corto.php)
 *
 * == entitycode
 * The key for the entity (example: 'main')
 */
$GLOBALS['metabase']['hosted'] = array(
        $GLOBALS['baseUrl']."wayf" => array(
            'infilter' => 'infilter',
            'outfilter' => 'outfilter',
            'keepsession' => false,
        ),
        $GLOBALS['baseUrl']."idp1" => array(
            #'idp' => $GLOBALS['baseUrl']."null",
            'infilter' => 'infilter',
            'outfilter' => 'outfilter',
            'keepsession' => true,
        ),
        $GLOBALS['baseUrl']."idp2" => array(
            #'IDPList' =>  array($GLOBALS['baseUrl']."idp1"),
            #'idp' => $GLOBALS['baseUrl'].'wayf',
            #'key' => 'server_pem',
            'auto' => true,
            'idp' => $GLOBALS['baseUrl']."null",
            'infilter' => 'infilter',
            'outfilter' => 'outfilter',
            #'keepsession' => true,
        ),
        $GLOBALS['baseUrl']."vidp1" => array(
            'virtual' => array($GLOBALS['baseUrl']."idp2", $GLOBALS['baseUrl']."idp1"),
            'key' => 'server_pem',
            'infilter' => 'infilter',
            'outfilter' => 'outfilter',
        ),
    );