#!/usr/bin/env php
<?php

require realpath(__DIR__ . '/../vendor') . '/autoload.php';

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Filter\JSMinPlusFilter;
use Assetic\Filter\CssMinFilter;

$jsFiles = array(
    '/javascript/respond.min.js' => false,
    '/javascript/jquery-1.10.2.min.js' => false,
    '/javascript/jquery.tmpl.min.js' => false,
    '/javascript/jquery.cookie.js' => true,
    '/javascript/matchMedia.js' => true,
    '/javascript/screen.js' => true,
    '/javascript/discover.js' => true);

$cssFiles = array(
    '/css/ext/jqueryjscrollpane/jquery.jscrollpane.css' => true,
    '/css/responsive/screen.css' => true);

$time = time();
$assetInfo = array();
foreach(array('css' => $cssFiles,'js' => $jsFiles) as $assetType => $files) {
    $assetCollection = array();

    foreach ($files as $file => $minify) {
        $filters = array();
        if ($minify) {
            $filters[] = ($assetType == 'css' ? new CssMinFilter() : new JSMinPlusFilter());
        }
        $assetCollection[] = new FileAsset('../www/authentication' . $file, $filters);
    }

    $js = new AssetCollection($assetCollection);

    $asset = 'generated/' . $assetType . '/' . $time;
    $dir = '../www/authentication/' . $asset;
    mkdir($dir, 0777, true);
    $assetFile = $dir . '/' . $assetType . '.min.' . $assetType;
    file_put_contents($assetFile, $js->dump());

    $assetInfo[$assetType] = array(
        "static" => array('/' . $asset . '/' . $assetType . '.min.' . $assetType),
        "dynamic" => array_keys($files));

}

file_put_contents('../www/authentication/assets.json', json_encode($assetInfo));



