<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */

$metadata['https://engine.surfconext.nl/authentication/idp/metadata'] = array(
        'SingleSignOnService'  => 'https://engine.surfconext.nl/authentication/idp/single-sign-on',
        'certificate'=>'server.crt',
        'name' => array('en'=>'EngineBlock (prod)'),
);

$localConfig = __DIR__ . '/saml20-idp-remote.local.php';
if (file_exists($localConfig)) {
    require $localConfig;
}
