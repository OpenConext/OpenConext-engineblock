#!/usr/bin/env php
<?php
require realpath(__DIR__ . '/../vendor') . '/autoload.php';

define ('__LOGO_DIR__', __DIR__ . '/../www/authentication/cached');

function getInput() {
  $handle = fopen ("php://stdin","r");
  $line = fgets($handle);
  return trim($line);
}

function getFromCurl($urlSuffix, $serviceRegistry) {
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $serviceRegistry->url . $urlSuffix);
  curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($curl, CURLOPT_HTTPGET, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  if (!empty($serviceRegistry->user)) {
    $userstring = $serviceRegistry->user;
    if (!empty($serviceRegistry->password)) {
      $userstring.= ':' . $serviceRegistry->password;
    }
    curl_setopt($curl, CURLOPT_USERPWD, $userstring);
  }

  $data = curl_exec($curl);
  if (curl_errno($curl)) {
    print "Error: " . curl_error($curl) . " on " . $serviceRegistry->url . $urlSuffix;
    exit(1);
  }

  $data = json_decode($data);
  curl_close($curl);
  return $data;
}

function loadService($id, $serviceRegistry) {
  return getFromCurl("/$id", $serviceRegistry);
}

function createThumb($logoUrl) {
  $cachedName = md5($logoUrl);
  $cachedLocation = __LOGO_DIR__ . "/" . $cachedName . ".png";
  $image = @file_get_contents($logoUrl);
  if (!$image) {
    return;
  }
  file_put_contents($cachedLocation, $image);
  try {
    $image = \PHPImageWorkshop\ImageWorkshop::initFromPath($cachedLocation);
    $image->resizeInPixel(64, null, true);
    $image->save(__LOGO_DIR__, $cachedName . ".png");
  } catch(Exception $e) {
    echo $e->getMessage();
  }
}

$options = "s:u:p:";
$inputOptions = getopt($options);

$registry = new stdClass();
$registry->url = array_key_exists('s', $inputOptions) ? $inputOptions['s'] : "";
$registry->user = array_key_exists('u', $inputOptions) ? $inputOptions['u'] : "";
$registry->password = array_key_exists('p', $inputOptions) ? $inputOptions['p'] : "";

if ($registry->url === "") {
  $registry->url = "https://serviceregistry.surfconext.nl/janus/app.php/api/connections";
}

if (!array_key_exists('s', $inputOptions)) {
  echo "What is the Service Registry URL (default: " . $registry->url . "):";
  $url = getInput();
  if (!empty($url)) {
    $registry->url = $url;
  }
}

if (!array_key_exists('u', $inputOptions)) {
  echo "What is the username for the SR (default: <empty>):";
  $registry->user = getInput();
}

if (!array_key_exists('p', $inputOptions)) {
  echo "What is the password for the SR (default: <empty>):";
  $registry->password = getInput();
}

if (count($inputOptions) === 0) {
  echo "You can also invoke this command with:\n";
  echo "-s <url>: Service Registry URL to the connections JSON\n";
  echo "-u <username>: Service Registry HTTP username\n";
  echo "-p <password>: Service Registry HTTP password\n";
}

$data = getFromCurl('', $registry);

if (!property_exists($data, 'connections')) {
  echo "Error: Invalid response, the key connections is not available";
  exit(2);
}

foreach ($data->connections as $srItem) {
  if (property_exists($srItem, 'id')) {
    $srId = $srItem->id;
    $serviceData = loadService($srId, $registry);
    $logo = @$serviceData->metadata->logo[0]->url;
    if (!is_null($logo)) {
      createThumb($logo);
    }
  }
}