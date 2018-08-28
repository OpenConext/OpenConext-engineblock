#!/usr/bin/env php
<?php

use OpenConext\EngineBlockBridge\Configuration\EngineBlockConfiguration;
use OpenConext\EngineBlockBridge\Configuration\EngineBlockIniFileLoader;

/*
 * This should be run in the VM in order to dump required INI configuration values to a YAML parameter file that
 * can be used by Symfony
 */

$currentPath  = realpath(__DIR__);
$projectRoot  = $currentPath . '/../../';
$autoloadPath = $projectRoot . 'vendor/autoload.php';
$ymlDumpPath  = $projectRoot . 'app/config/ini_parameters.yml';

require_once($autoloadPath);

$loader     = new EngineBlockIniFileLoader();
$iniContent = $loader->load(
    array(
        $projectRoot . 'application/' . EngineBlockIniFileLoader::CONFIG_FILE_DEFAULT,
        EngineBlockIniFileLoader::CONFIG_FILE_ENVIRONMENT,
    )
);

$config = new EngineBlockConfiguration($iniContent);

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
 * Which is parsed by `parse_ini_file` in EngineBlockIniFileLoader as:
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

/**
 * Escape '%' signs in YAML values.
 *
 * Without escaping them, '%test%' will be interpreted as a DI parameter.
 */
function escapeYamlValue($value)
{
    return str_replace('%', '%%', $value);
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
        //    hostname = engine.vm.openconext.org
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

        'api.users.metadataPush.username'                         => $config->get('engineApi.users.metadataPush.username'),
        'api.users.metadataPush.password'                         => escapeYamlValue($config->get('engineApi.users.metadataPush.password')),
        'api.users.profile.username'                              => $config->get('engineApi.users.profile.username'),
        'api.users.profile.password'                              => escapeYamlValue($config->get('engineApi.users.profile.password')),
        'api.users.deprovision.username'                          => $config->get('engineApi.users.deprovision.username'),
        'api.users.deprovision.password'                          => escapeYamlValue($config->get('engineApi.users.deprovision.password')),
        'pdp.host'                                                => $config->get('pdp.host'),
        'pdp.policy_decision_point_path'                          => $config->get('pdp.policy_decision_point_path'),
        'pdp.username'                                            => $config->get('pdp.username'),
        'pdp.password'                                            => escapeYamlValue($config->get('pdp.password')),
        'pdp.client_id'                                           => $config->get('pdp.client_id'),
        'attribute_aggregation.base_url'                          => $config->get('attributeAggregation.baseUrl'),
        'attribute_aggregation.username'                          => $config->get('attributeAggregation.username'),
        'attribute_aggregation.password'                          => escapeYamlValue($config->get('attributeAggregation.password')),

        // Logger settings.
        'logger.channel'                                          => $config->get('logger.conf.name'),
        'logger.fingers_crossed.passthru_level'                   => $config->get('logger.conf.handler.fingers_crossed.conf.passthru_level'),
        'logger.fingers_crossed.action_level'                     => $config->get('logger.conf.handler.fingers_crossed.conf.activation_strategy.conf.action_level'),
        'logger.syslog.ident'                                     => $config->get('logger.conf.handler.syslog.conf.ident'),
        'logger.line_format'                                      => escapeYamlValue($config->get('logger.conf.handler.syslog.conf.formatter.conf.format')),

        'database.host'                                           => $config->get('database.host'),
        'database.port'                                           => $config->get('database.port'),
        'database.user'                                           => $config->get('database.user'),
        'database.password'                                       => escapeYamlValue($config->get('database.password')),
        'database.dbname'                                         => $config->get('database.dbname'),
        'database.test.host'                                      => $config->get('database.test.host'),
        'database.test.port'                                      => $config->get('database.test.port'),
        'database.test.user'                                      => $config->get('database.test.user'),
        'database.test.password'                                  => escapeYamlValue($config->get('database.test.password')),
        'database.test.dbname'                                    => $config->get('database.test.dbname'),

        // Generic cookie settings.
        'cookie.path'                                             => $config->get('cookie_path', '/'),
        'cookie.secure'                                           => (bool) $config->get('use_secure_cookies', true),

        // Cookie settings specific to the language cookie.
        'cookie.locale.domain'                                    => $config->get('cookie.lang.domain'),
        'cookie.locale.expiry'                                    => (int) $config->get('cookie.lang.expiry'),
        'cookie.locale.http_only'                                 => (bool) $config->get('cookie.lang.http_only', false),
        'cookie.locale.secure'                                    => (bool) $config->get('cookie.lang.secure', false),

        'feature_eb_encrypted_assertions'                         => (bool) $config->get('engineblock.feature.encrypted_assertions'),
        'feature_eb_encrypted_assertions_require_outer_signature' => (bool) $config->get('engineblock.feature.encrypted_assertions_require_outer_signature'),
        'feature_api_metadata_push'                               => (bool) $config->get('engineApi.features.metadataPush'),
        'feature_api_consent_listing'                             => (bool) $config->get('engineApi.features.consentListing'),
        'feature_api_metadata_api'                                => (bool) $config->get('engineApi.features.metadataApi'),
        'feature_api_deprovision'                                 => (bool) $config->get('engineApi.features.deprovision'),
        'feature_run_all_manipulations_prior_to_consent'          => (bool) $config->get('engineblock.feature.run_all_manipulations_prior_to_consent'),
        'feature_block_user_on_violation'                         => (bool) $config->get('engineblock.feature.block_user_on_violation'),
        'minimum_execution_time_on_invalid_received_response'     => (int) $config->get('minimumExecutionTimeOnInvalidReceivedResponse'),
        'wayf.cutoff_point_for_showing_unfiltered_idps'           => (int) $config->get('wayf.cutoffPointForShowingUnfilteredIdps'),
        'wayf.remember_choice'                                    => (bool) $config->get('wayf.rememberChoice', false),
        'time_frame_for_authentication_loop_in_seconds'           => (int) $config->get('engineblock.timeFrameForAuthenticationLoopInSeconds'),
        'maximum_authentication_procedures_allowed'               => (int) $config->get('engineblock.maximumAuthenticationProceduresAllowed'),
        'view_default_title'                                      => $config->get('defaults.title'),
        'view_default_header'                                     => $config->get('defaults.header'),
        'view_default_logo'                                       => $config->get('defaults.logo'),
        'ui_return_to_sp_link'                                    => (bool) $config->get('ui.return_to_sp_link.active'),

        // Store attributes with their values, meaning that if an Idp suddenly
        // sends a new value (like a new e-mail address) consent has to be
        // given again.
        'consent_store_values'                                    => (bool) $config->get('authentication.storeValues', true),

        // Edugain metadata
        'edugain'                                                 => $config->get('edugain', array())->toArray(),

        // Guest qualifier for the AddGuestStatus filter.
        'addgueststatus_guestqualifier'                           => $config->get('addgueststatus.guestqualifier', ''),

        // OpenConext support and terms-of-use URL.
        // Note the escaping of '%' to support URLs like '/Terms+of+Service+%28EN%29'.
        'openconext_support_url'                                  => escapeYamlValue($config->get('openconext.supportUrl')),
        'openconext_terms_of_use_url'                             => escapeYamlValue($config->get('openconext.termsOfUse')),
        'openconext_support_name_id_url'                          => escapeYamlValue($config->get('openconext.supportUrlNameId')),

        // Email configuration
        'email_request_access_address'                            => $config->get('email.help'),
        'email_idp_debugging'                                     => $config->get('email.idpDebugging')->toArray(),

        // Profile.
        'profile_base_url'                                        => $config->get('profile.baseUrl', ''),

        // Path to the attribute definition file. Note the
        // backwards-compatible support for using the app root constant.
        'attribute_definition_file_path'                          => str_replace(
            'ENGINEBLOCK_FOLDER_APPLICATION',
            $projectRoot . 'application/',
            $config->get(
                'attributeDefinitionFile', $projectRoot . 'application/configs/attributes-v2.2.0.json'
            )
        ),
        'monitor_database_health_check_query'                     => $config->get('openconext.monitor_bundle_health_query', '')
    )
);

$ymlToDump = \Symfony\Component\Yaml\Yaml::dump($ymlContent, 4, 4);

$comment = "# This file is auto-generated" . PHP_EOL;

$writer = new \Symfony\Component\Filesystem\Filesystem();
$writer->dumpFile($ymlDumpPath, $comment . $ymlToDump, 0664);
