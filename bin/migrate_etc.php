#!/usr/bin/env php
<?php

$defaultConfigFile = realpath(__DIR__ . '/../application/configs/application.ini');
$defaultConfig  = parse_ini_file($defaultConfigFile,INI_SCANNER_RAW);
$localConfig    = parse_ini_file('/etc/surfconext/engineblock.ini', INI_SCANNER_RAW);

$newLocalConfig = <<<CONFIG
; OpenConext EngineBlock Local configuration
;
; For more information on possible configuration options see:
; $defaultConfigFile
;

CONFIG;
foreach ($localConfig as $sectionName => $sectionVars) {
    ksort($sectionVars);
    $newLocalConfig .= "[$sectionName]\n";
    $date = date('Ymd');

    foreach ($sectionVars as $sectionVarName => $sectionVarValue) {
        if ($sectionVarName === 'encryption.key.private') {
            file_put_contents('/etc/surfconext/engineblock.key', $sectionVarValue);
            $newLocalConfig .= "encryption.keys.$date.publicFile = /etc/surfconext/engineblock.$date.key\n";
            continue;
        }
        if ($sectionVarName === 'encryption.key.public') {
            file_put_contents('/etc/surfconext/engineblock.pem', $sectionVarValue);
            $newLocalConfig .= "encryption.keys.$date.privateFile = /etc/surfconext/engineblock.$date.pem\n";
            continue;
        }
        if ($sectionVarName === 'auth.simplesamlphp.idp.cert') {
            $newLocalConfig .= "auth.simplesamlphp.idp.certificate = /etc/surfconext/engineblock.$date.pem\n";
            continue;
        }
        $matches = array();
        if (preg_match('/encryption\.keys\.(?P<keyid>.+).public/', $sectionVarName, $matches) > 0) {
            $fileName = "/etc/surfconext/engineblock.{$matches['keyid']}.pem";
            file_put_contents($fileName, $sectionVarValue);
            $newLocalConfig .= "{$sectionVarName}File = $fileName\n";
            continue;
        }

        if (
            !isset($defaultConfig['base'][$sectionVarName]) &&
            strpos($sectionVarName, 'encryption.keys.') !==0 &&
            strpos($sectionVarName, 'logs.file.')!==0 &&
            strpos($sectionVarName, 'database.')!==0 &&
            strpos($sectionVarName, 'serviceRegistry.caching') !==0
        ) {
            echo "|$sectionVarName| (section: [$sectionName]) is not registered in the default application.ini, removing\n";
            continue;
        }
        $baseVarValue = null;
        if (isset($defaultConfig['base'][$sectionVarName])) {
            $baseVarValue = $defaultConfig['base'][$sectionVarName];
        }

        if (is_array($sectionVarValue)) {
            if (is_null($baseVarValue)) {
                $baseVarValue = array();
            }
            $diff = array_diff($sectionVarValue, $baseVarValue);
            if (!empty($diff)) {
                foreach ($sectionVarValue as $sectionVarSubValue) {
                    $newLocalConfig .= "{$sectionVarName}[] = $sectionVarSubValue\n";
                }
                continue;
            }
        }
        else if ($baseVarValue !== $sectionVarValue) {
            if (empty($sectionVarValue)) {
                $sectionVarValue = 'false';
            }
            $newLocalConfig .= "{$sectionVarName} = $sectionVarValue\n";
            continue;
        }

        echo "|$sectionVarName| is redundant, removing (section: [$sectionName]):" .
            PHP_EOL.
            "Default: " . print_r($sectionVarValue, true) . PHP_EOL .
            "Local: " . print_r($baseVarValue, true) . PHP_EOL . PHP_EOL;
    }
}

$newConfigFile = '/etc/surfconext/engineblock.ini.new';
file_put_contents($newConfigFile, $newLocalConfig);
echo <<<FINAL_MESSAGE
-----------------------------------------
WROTE $newConfigFile
PLEASE REVIEW MANUALLY AND RUN:
sudo install -b /etc/surfconext/engineblock.ini.new /etc/surfconext/engineblock.ini
-----------------------------------------

FINAL_MESSAGE;
