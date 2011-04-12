<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */

$metadata['https://eb-public.ebdev.net/authentication/idp/metadata'] = array(
        'SingleSignOnService'  => 'https://eb-public.ebdev.net/authentication/idp/single-sign-on',
        'certificate'=>'server.crt',
        'name' => array('en'=>'EngineBlock'),
);

#die('SAML2.0 remote Idp not configured yet! Please edit metadata/saml20-idp-remote.php and replace <ENGINEBLOCK_HOST> with the proper hostname and remove this message');
