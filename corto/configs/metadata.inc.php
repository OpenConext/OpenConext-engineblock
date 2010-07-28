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
 * @var $GLOBALS['metabase'] Metadata configuration variable
 */
$GLOBALS['metabase'] = array();

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

/**
 * = remote
 * Array with remote servers, each server has the URL it is hosted on as array key.
 * example:
 * $GLOBALS['metabase']['remote']=> array('http://remote_idp.example.com'=>...));
 *
 * As value it can have an array with one or more of the following configuration options:
 *
 * == sharedkey
 * Shared secret used to sign the message
 *
 * == spfilter
 *
 * == AssertionConsumerService
 *
 * == SingleSignOnService
 *
 * == ArtifactResolutionService
 *
 * == filter
 * Called before
 *
 * == key
 *
 * == WantAuthnRequestsSigned
 *
 * == WantResponsesSigned
 *
 */
$GLOBALS['metabase']['remote'] = array(
        $GLOBALS['baseUrl'].'main' => array(
            'sharedkey' => 'abracadabra',
            'spfilter' => 'spfilter',
            'AssertionConsumerService' => array(
                'Location' => $GLOBALS['baseUrl']."main/demoapp",
                'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post',
            ),
        ),
        $GLOBALS['baseUrl']."wayf" => array(
            #'WantAssertionsSigned'    => true,
            #'AuthnRequestsSigned'    => true,
            'AssertionConsumerService' => array(
                'Location' => $GLOBALS['baseUrl']."wayf/assertionConsumerService",
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ),
            'SingleSignOnService' => array(
                'Binding'     => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location'     => $GLOBALS['baseUrl'].'wayf/singleSignOnService',
            ),
            'ArtifactResolutionService' => array(
                'Binding'    => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'Location' => $GLOBALS['baseUrl'].'wayf/ArtifactResolutionService',
            ),
            'filter' => 'spfilter',
            'key' => 'server_pem',
        ),
        $GLOBALS['baseUrl'].'null' => array(
            'SingleSignOnService' => array(
                'Binding'     => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location'     => $GLOBALS['baseUrl'].'null/nullSingleSignOnService',
                #'Location'     => $GLOBALS['baseUrl'].'null/autoSingleSignOnService',
            ),

        ),
        $GLOBALS['baseUrl']."idp1" => array(
            'AssertionConsumerService' => array(
                'Location' => $GLOBALS['baseUrl']."idp1/assertionConsumerService",
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ),
            'SingleSignOnService' => array(
                'Binding'     => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location'     => $GLOBALS['baseUrl'].'idp1/singleSignOnService',
            ),
            'ArtifactResolutionService' => array(
                'Binding'    => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'Location' => $GLOBALS['baseUrl'].'idp1/ArtifactResolutionService',
            ),
            'filter' => 'idpfilter',
            'WantAuthnRequestsSigned' =>  true,
            #'publickey' => 'server_crt',
        ),
        $GLOBALS['baseUrl']."idp2" => array(
            'AssertionConsumerService' => array(
                'Location' => $GLOBALS['baseUrl']."idp2/assertionConsumerService",
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ),
            'SingleSignOnService' => array(
                'Binding'     => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location'     => $GLOBALS['baseUrl'].'idp2/singleSignOnService',
            ),
            'ArtifactResolutionService' => array(
                'Binding'    => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'Location' => $GLOBALS['baseUrl'].'idp2/ArtifactResolutionService',
            ),
            'publickey' => 'wayfwildcard',
            'filter' => 'spfilter',
        ),
        $GLOBALS['baseUrl']."vidp1" => array(
            'AssertionConsumerService' => array(
                'Location' => $GLOBALS['baseUrl']."vidp1/handleVirtualIDP",
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            ),
            'SingleSignOnService' => array(
                'Binding'     => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location'     => $GLOBALS['baseUrl'].'vidp1/handleVirtualIDP',
            ),
            'ArtifactResolutionService' => array(
                'Binding'    => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                'Location' => $GLOBALS['baseUrl'].'vidp1/ArtifactResolutionService',
            ),
            'publickey' => 'server_crt',
            'filter' => 'spfilter',
        ),
        'http://jach-idp.test.wayf.dk/saml2/idp/metadata.php' => Array (
            'SingleSignOnService' => array(
                'Binding'     => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location'     => 'http://jach-idp.test.wayf.dk/saml2/idp/SSOService.php',
            ),
            'filter' => 'idpfilter',
            'publickey' => 'wayfwildcard',
        ),
        'http://jach-idp.test.wayf.dk/saml2/idp/metadata.php' => Array (
            'SingleSignOnService' => array(
                'Binding'     => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location'     => 'http://jach-idp.test.wayf.dk/saml2/idp/SSOService.php',
            ),
            'filter' => 'idpfilter',
            'publickey' => 'wayfwildcard',
        ),
        'https://orphanage.wayf.dk' => Array (
            'SingleSignOnService' => array(
                'Binding'     => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location'     => 'https://orphanage.wayf.dk/saml2/idp/SSOService.php',
            ),
            'filter' => 'idpfilter',
            'publickey' => 'wayfwildcard',
        ),
        'https://pure.wayf.ruc.dk/myWayf/module.php/saml/sp/metadata.php/default-sp' => array(
            'WantResponsesSigned' => true,
            'AssertionConsumerService' => array (
                  'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                  'Location' => 'https://pure.wayf.ruc.dk/myWayf/module.php/saml/sp/saml2-acs.php/default-sp',
                ),
        ),
);