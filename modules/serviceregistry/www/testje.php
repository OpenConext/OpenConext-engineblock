<?php
/**
 *
 */

require __DIR__ . '/_includes.php';

$url = new OpenSsl_Url('https://apps.ibuildings.com');
$url->connect();
$url->getParsedOutput();