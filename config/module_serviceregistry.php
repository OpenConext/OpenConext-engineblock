<?php
/* 
 * The configuration of SURFconext Service Registry package
 */

$config = array (
    'metadata_refresh_cron_tags'      => array('hourly'),
    'validate_entity_certificate_cron_tags' => array('daily'),
    'validate_entity_endpoints_cron_tags' => array('daily'),
    'ca_bundle_file' => '/etc/pki/tls/certs/ca-bundle.crt',
);

$localConfig = '/etc/surfconext/serviceregistry.module_serviceregistry.php';
if (file_exists($localConfig)) {
    require $localConfig;
}