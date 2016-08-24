<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */


require_once __DIR__. '/../../../../library/EngineBlock/ApplicationSingleton.php';
$application = EngineBlock_ApplicationSingleton::getInstance();

$appConfig = $application->getConfiguration();

$metadata[$appConfig->auth->simplesamlphp->idp->entityId] = array(
    'entityid' => $appConfig->auth->simplesamlphp->idp->entityId,
    'SingleSignOnService' =>
    array (
        0 => array (
            'Binding'  => $appConfig->auth->simplesamlphp->idp->binding,
            'Location' => $appConfig->auth->simplesamlphp->idp->location,
        ),
    ),
    'certificate' => $appConfig->auth->simplesamlphp->idp->certificate,
);
