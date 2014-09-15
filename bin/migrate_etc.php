#!/usr/bin/env php
<?php

// Our default configuration values.
$defaultConfigFile = realpath(__DIR__ . '/../application/configs/application.ini');
$defaultConfig  = parse_ini_file($defaultConfigFile,INI_SCANNER_RAW);

// The local overrides.
$localConfig    = parse_ini_file('/etc/surfconext/engineblock.ini', INI_SCANNER_RAW);

// Header for the new local configuration file.
$newLocalConfig = <<<CONFIG
; OpenConext EngineBlock Local configuration
;
; For more information on possible configuration options see:
; $defaultConfigFile
;

CONFIG;
foreach ($localConfig as $sectionName => $sectionVars) {
    if (empty($sectionVars)) {
        continue;
    }

    // Sort alphabetically
    ksort($sectionVars);

    // Start with section header (ignored)
    $newLocalConfig .= "[local]\n";

    // Set the keyId for the default key to the current date in Year Month Day format.
    $defaultKeyId = 'default';

    foreach ($sectionVars as $sectionVarName => $sectionVarValue) {

        // Move the default private key to a file.
        if ($sectionVarName === 'encryption.key.private') {
            $filePath = "/etc/surfconext/engineblock.key";
            echo "|$sectionVarName| Writing out default private key to $filePath and setting encryption.keys.$defaultKeyId.privateFile";
            file_put_contents($filePath, $sectionVarValue);
            $newLocalConfig .= "encryption.keys.$defaultKeyId.privateFile = $filePath\n";
            continue;
        }

        // Move the default private key to a file.
        if ($sectionVarName === 'encryption.key.privateFile') {
            $filePath = "/etc/surfconext/engineblock.key";
            echo "|$sectionVarName| Moving default private key to $filePath and setting encryption.keys.$defaultKeyId.privateFile";
            rename($sectionVarValue, $filePath);
            $newLocalConfig .= "encryption.keys.$defaultKeyId.privateFile = $filePath\n";
            continue;
        }

        // Move the default public key to a file.
        if ($sectionVarName === 'encryption.key.public') {
            $filePath = "/etc/surfconext/engineblock.crt";
            file_put_contents($filePath, $sectionVarValue);
            echo "|$sectionVarName| Writing out default public key to $filePath and setting encryption.keys.$defaultKeyId.publicFile";
            $newLocalConfig .= "encryption.keys.$defaultKeyId.publicFile = $filePath\n";
            continue;
        }

        // Make SimpleSAMLphp (used by Profile) use the file based public key.
        if ($sectionVarName === 'auth.simplesamlphp.idp.cert') {
            $newLocalConfig .= "auth.simplesamlphp.idp.certificate = /etc/surfconext/engineblock.crt\n";
            continue;
        }

        // Move any 'extra' public keys to a file as well.
        // Note that we leave .publicFile alone.
        $matches = array();
        if (preg_match('/encryption\.keys\.(?P<keyid>.+).public$/', $sectionVarName, $matches) > 0) {
            $fileName = "/etc/surfconext/engineblock.{$matches['keyid']}.pem.crt";
            file_put_contents($fileName, $sectionVarValue);
            $newLocalConfig .= "{$sectionVarName}File = $fileName\n";
            continue;
        }

        // Move any 'extra' private keys to a file as well.
        // Note that we leave .privateFile alone.
        if (preg_match('/encryption\.keys\.(?P<keyid>.+).private$/', $sectionVarName, $matches) > 0) {
            $fileName = "/etc/surfconext/engineblock.{$matches['keyid']}.pem.key";
            file_put_contents($fileName, $sectionVarValue);
            $newLocalConfig .= "{$sectionVarName}File = $fileName\n";
            continue;
        }

        // Remove any configuration values that are not (or no longer) in the base configuration.
        if (
            !isset($defaultConfig['base'][$sectionVarName]) &&
            // Unless... it's a configuration value for which there is or can be no default:
            strpos($sectionVarName, 'encryption.keys.') !==0 &&
            strpos($sectionVarName, 'logs.file.')!==0 &&
            strpos($sectionVarName, 'database.')!==0 &&
            strpos($sectionVarName, 'serviceRegistry.caching') !==0
        ) {
            echo "|$sectionVarName| (section: [$sectionName]) is not registered in the default application.ini, removing\n";
            continue;
        }

        // If there is no base value, default to null.
        $baseVarValue = null;
        if (isset($defaultConfig['base'][$sectionVarName])) {
            $baseVarValue = $defaultConfig['base'][$sectionVarName];
        }

        // If this is an array value (like database.masters[] ):
        if (is_array($sectionVarValue)) {
            // Check if the default was empty, if so it should be an empty array.
            if (is_null($baseVarValue)) {
                $baseVarValue = array();
            }
            // See if there are any changes.
            $diff = array_diff($sectionVarValue, $baseVarValue);

            // If there are then copy it into the new local config.
            if (!empty($diff)) {
                foreach ($sectionVarValue as $sectionVarSubValue) {
                    $newLocalConfig .= "{$sectionVarName}[] = \"$sectionVarSubValue\"\n";
                }
                continue;
            }
            // Otherwise it will be removed.
        }
        else if ($baseVarValue !== $sectionVarValue) {
            // 0, null, false these are all the same to the INI parser,
            // but 'caching.lifetime = false' is weird
            // so we put down the one least likely to be wrong: 0
            if (empty($sectionVarValue)) {
                $newLocalConfig .= "{$sectionVarName} = 0\n";
            }
            // write out numeric values as literals.
            else if (is_numeric($sectionVarValue)) {
                $newLocalConfig .= "{$sectionVarName} = $sectionVarValue\n";
            }
            // everything else is strings.
            else {
                $newLocalConfig .= "{$sectionVarName} = \"$sectionVarValue\"\n";
            }

            continue;
        }

        echo "|$sectionVarName| is redundant, removing (section: [$sectionName]):" .
            PHP_EOL.
            "Default: " . print_r($sectionVarValue, true) . PHP_EOL .
            "Local: " . print_r($baseVarValue, true) . PHP_EOL . PHP_EOL;
    }
}

// Write out the new configuration file.
$newConfigFile = '/etc/surfconext/engineblock.ini.new';
file_put_contents($newConfigFile, $newLocalConfig);

// Deliver a final message.
echo <<<FINAL_MESSAGE
-----------------------------------------
WROTE $newConfigFile
PLEASE REVIEW MANUALLY AND RUN:
sudo install -b /etc/surfconext/engineblock.ini.new /etc/surfconext/engineblock.ini
-----------------------------------------

FINAL_MESSAGE;
exit(0);
