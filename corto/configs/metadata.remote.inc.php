<?php


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