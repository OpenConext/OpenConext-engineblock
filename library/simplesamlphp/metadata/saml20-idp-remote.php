<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */


require_once 'Surfnet/Application.php';
$application = new Surfnet_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$appConfig = $application->getConfig();

$metadata[$appConfig->auth->simplesamlphp->idp->entityId] = array(
    'entityid' => $appConfig->auth->simplesamlphp->idp->entityId,
    'SingleSignOnService' =>
    array (
        0 => array (
            'Binding'  => $appConfig->auth->simplesamlphp->idp->binding,
            'Location' => $appConfig->auth->simplesamlphp->idp->location,
        ),
    ),
    'keys' => array(
        array(
            'signing'=>true,
            'type' => 'X509Certificate',
            'X509Certificate' => $appConfig->auth->simplesamlphp->idp->cert,
        )
    ),
);
