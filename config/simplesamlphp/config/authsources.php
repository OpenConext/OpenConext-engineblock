<?php
/**
 * Authentication source settings.
 *
 * To keep all configuration as much as possible in one place:
 * Use the applications authentication.ini
 */

require_once __DIR__. '/../../../../library/EngineBlock/ApplicationSingleton.php';
$application = EngineBlock_ApplicationSingleton::getInstance();
$application->bootstrap();
$appConfig = $application->getConfiguration();
if (!isset($appConfig->auth->simplesamlphp->idp->entityId) || !$appConfig->auth->simplesamlphp->idp->entityId) {
    throw new EngineBlock_Exception('No entityId for Idp to use?');
}

$config = array(

	// This is a authentication source which handles admin authentication.
	'admin' => array(
		// The default is to use core:AdminPassword, but it can be replaced with
		// any authentication source.

		'core:AdminPassword',
	),


	// An authentication source which can authenticate against both SAML 2.0
	// and Shibboleth 1.3 IdPs.
	'default-sp' => array(
		'saml:SP',
                'authproc' => array(
                    20 => array(
                        'class' => 'saml:NameIDAttribute',
                        'format' => '%V',
                    ),
                ),

		// The entity ID of this SP.
		// Can be NULL/unset, in which case an entity ID is generated based on the metadata URL.
		'entityID' => NULL,

		// The entity ID of the IdP this should SP should contact.
		// Can be NULL/unset, in which case the user will be shown a list of available IdPs.
		'idp' => $appConfig->auth->simplesamlphp->idp->entityId,

		// The URL to the discovery service.
		// Can be NULL/unset, in which case a builtin discovery service will be used.
		'discoURL' => NULL,
	),
);
