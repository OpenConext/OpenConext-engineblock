<?php
/* 
 * Configuration for the Cron module.
 * 
 * $Id: $
 */

$config = array (
	'key'           => 'DrAXe6as',
	'allowed_tags'  => array('daily', 'hourly', 'frequent'),
	'debug_message' => TRUE,
	'sendemail'     => FALSE,
);

$localConfig = '/etc/surfconext/serviceregistry.module_cron.php';
if (file_exists($localConfig)) {
    require $localConfig;
}