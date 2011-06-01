<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
 *
 * See: https://rnd.feide.no/content/idp-remote-metadata-reference
 */

$localConfig = '/etc/surfconext/serviceregistry.saml20-idp-remote.php';
if (!file_exists($localConfig)) {
    die('No remote IDP configuration file at ' . $localConfig);
}
require $localConfig;