#!/usr/bin/env php
<?php

/*
 * This should be run in the VM in order to dump required INI configuration values to a YAML parameter file that
 * can be used by Symfony
 */

$currentPath  = realpath(__DIR__);
$projectRoot  = $currentPath . '/../../';
$autoloadPath = $projectRoot . 'vendor/autoload.php';
$ymlDumpPath  = $projectRoot . 'app/config/ini_parameters.yml';

require_once($autoloadPath);

$loader     = new OpenConext\EngineBlockBridge\Configuration\EngineBlockIniFileLoader();
$iniContent = $loader->load(
    array(
        $projectRoot . 'application/' . EngineBlock_Application_Bootstrapper::CONFIG_FILE_DEFAULT,
        EngineBlock_Application_Bootstrapper::CONFIG_FILE_ENVIRONMENT,
    )
);

$config = new \OpenConext\EngineBlockBridge\Configuration\EngineBlockConfiguration($iniContent);

$trustedProxies = $config->get('trustedProxyIps', array());
if (!is_array($trustedProxies)) {
    $trustedProxies = $trustedProxies->toArray();
}

$encryptionKeys = $config->get('encryption.keys', array());
if (!is_array($encryptionKeys)) {
    $encryptionKeys = $encryptionKeys->toArray();
}

$forbiddenSignatureMethodsCommaSeparated = $config->get('forbiddenSignatureMethods', '');
$forbiddenSignatureMethods = array_filter(
    array_map(
        'trim',
        explode(',', $forbiddenSignatureMethodsCommaSeparated)
    )
);

/**
 * Convert PHP-ini configuration in EB-ini format to yaml format.
 *
 * In EB-ini format, php-settings were defined like this:
 *
 *     phpSettings.memory_limit = "128M"
 *     phpSettings.date.timezone = "Europe/Amsterdam"
 *
 * Which was parsed by Zend_Config as:
 *
 *     [
 *         "memory_limit" => "128M",
 *         "date" => [
 *              "timezone" => "Europe/Amsterdam"
 *         ]
 *     ]
 *
 * The EngineBlock code responsible for actually calling ini_set then had
 * complicated logic (see below) to flatten the structure. We have now moved
 * that logic to here, so the YAML configuration used is a simple list of
 * PHP-ini settings:
 *
 *     phpSettings:
 *         memory_limit: "128M"
 *         date.timezone: "Europe/Amsterdam"
 */
function legacyPhpSettingsToYaml($legacySettings, &$flatSettings = [], $prefix = '')
{
    foreach ($legacySettings as $name => $value) {
        $prefixedName = $name;

        if (!empty($prefix)) {
            $prefixedName = $prefix . '.' . $name;
        }

        if (is_array($value)) {
            legacyPhpSettingsToYaml($value, $flatSettings, $prefixedName);
        } else {
            $flatSettings[$prefixedName] = $value;
        }
    }

    return $flatSettings;
}

$ymlContent = array(
    'parameters' => array(
        // Setting the debug mode to true will cause EngineBlock to display
        // more information about errors that have occurred and it will show
        // the messages it sends and receives for the authentication.
        //
        // NEVER TURN THIS ON FOR PRODUCTION!
        //
        // Note: this setting is independent from Symfony debug mode.
        'debug'                                                   => $config->get('debug', false),

        // Note: due to legacy reasons, hostname must be left empty (hostname
        // from the Host header will be used) or set to match the domain
        // setting. For example:
        //
        //    domain = vm.openconext.org
        //    hostname = empty, or engine.vm.openconext.org
        'domain'                                                  => $config->get('base_domain'),
        'hostname'                                                => $config->get('hostname'),

        'trusted_proxies'                                         => $trustedProxies,
        'encryption_keys'                                         => $encryptionKeys,

        // List of signature methods explicitly forbidden by EngineBlock.
        'forbidden_signature_methods'                             => $forbiddenSignatureMethods,

        // Ideally, PHP is configured using the regular PHP configuration in
        // /etc, but EngineBlock supports runtime modification of PHP
        // settings.
        'php_settings'                                            => legacyPhpSettingsToYaml($config->get('phpSettings', [])->toArray()),

        'api.users.janus.username'                                => $config->get('engineApi.users.janus.username'),
        'api.users.janus.password'                                => $config->get('engineApi.users.janus.password'),
        'api.users.profile.username'                              => $config->get('engineApi.users.profile.username'),
        'api.users.profile.password'                              => $config->get('engineApi.users.profile.password'),
        'pdp.host'                                                => $config->get('pdp.host'),
        'pdp.policy_decision_point_path'                          => $config->get('pdp.policy_decision_point_path'),
        'pdp.username'                                            => $config->get('pdp.username'),
        'pdp.password'                                            => $config->get('pdp.password'),
        'pdp.client_id'                                           => $config->get('pdp.client_id'),
        'attribute_aggregation.base_url'                          => $config->get('attributeAggregation.baseUrl'),
        'attribute_aggregation.username'                          => $config->get('attributeAggregation.username'),
        'attribute_aggregation.password'                          => $config->get('attributeAggregation.password'),
        'logger.channel'                                          => $config->get('logger.conf.name'),
        'logger.fingers_crossed.passthru_level'                   => $config->get(
            'logger.conf.handler.fingers_crossed.conf.passthru_level'
        ),
        'logger.syslog.ident'                                     => $config->get('logger.conf.handler.syslog.conf.ident'),
        'database.host'                                           => $config->get('database.host'),
        'database.port'                                           => $config->get('database.port'),
        'database.user'                                           => $config->get('database.user'),
        'database.password'                                       => $config->get('database.password'),
        'database.dbname'                                         => $config->get('database.dbname'),
        'database.test.host'                                      => $config->get('database.test.host'),
        'database.test.port'                                      => $config->get('database.test.port'),
        'database.test.user'                                      => $config->get('database.test.user'),
        'database.test.password'                                  => $config->get('database.test.password'),
        'database.test.dbname'                                    => $config->get('database.test.dbname'),
        'cookie.locale.domain'                                    => $config->get('cookie.lang.domain'),
        'cookie.locale.expiry'                                    => (int) $config->get('cookie.lang.expiry'),
        'cookie.locale.http_only'                                 => (bool) $config->get('cookie.lang.http_only', false),
        'cookie.locale.secure'                                    => (bool) $config->get('cookie.lang.secure', false),
        'feature_eb_encrypted_assertions'                         => (bool) $config->get('engineblock.feature.encrypted_assertions'),
        'feature_eb_encrypted_assertions_require_outer_signature' => (bool) $config->get('engineblock.feature.encrypted_assertions_require_outer_signature'),
        'feature_api_metadata_push'                               => (bool) $config->get('engineApi.features.metadataPush'),
        'feature_api_consent_listing'                             => (bool) $config->get('engineApi.features.consentListing'),
        'feature_api_metadata_api'                                => (bool) $config->get('engineApi.features.metadataApi'),
        'feature_run_all_manipulations_prior_to_consent'          => (bool) $config->get('engineblock.feature.run_all_manipulations_prior_to_consent'),
        'minimum_execution_time_on_invalid_received_response'     => (int) $config->get('minimumExecutionTimeOnInvalidReceivedResponse'),
        'wayf.cutoff_point_for_showing_unfiltered_idps'           => (int) $config->get('wayf.cutoffPointForShowingUnfilteredIdps'),
        'wayf.remember_choice'                                    => (bool) $config->get('wayf.rememberChoice', false),
        'time_frame_for_authentication_loop_in_seconds'           => (int) $config->get('engineblock.timeFrameForAuthenticationLoopInSeconds'),
        'maximum_authentication_procedures_allowed'               => (int) $config->get('engineblock.maximumAuthenticationProceduresAllowed'),
        'view_default_layout'                                     => $config->get('defaults.layout'),
        'view_default_title'                                      => $config->get('defaults.title'),
        'view_default_header'                                     => $config->get('defaults.header'),
        'ui_return_to_sp_link'                                    => (bool) $config->get('ui.return_to_sp_link.active'),

        // Store attributes with their values, meaning that if an Idp suddenly
        // sends a new value (like a new e-mail address) consent has to be
        // given again.
        'consent_store_values'                                    => (bool) $config->get('authentication.storeValues', true),

        // Edugain metadata
        'edugain'                                                 => $config->get('edugain', array())->toArray(),

        // Guest qualifier for the AddGuestStatus filter.
        'addgueststatus_guestqualifier'                           => $config->get('addgueststatus.guestqualifier', ''),
    )
);

$ymlToDump = \Symfony\Component\Yaml\Yaml::dump($ymlContent, 4, 4);

$comment = "# This file is auto-generated" . PHP_EOL;

$writer = new \Symfony\Component\Filesystem\Filesystem();
$writer->dumpFile($ymlDumpPath, $comment . $ymlToDump, 0664);
