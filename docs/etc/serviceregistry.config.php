<?php

/**
 * REQUIRED environment configuration for Service Registry (SimpleSAMLphp)
 * For a complete set of the available configuration options
 * please see serviceregistry/config/config.php
 */

/*
 * The timezone of the server. This option should be set to the timezone you want
 * simpleSAMLphp to report the time in. The default is to guess the timezone based
 * on your system timezone.
 *
 * See this page for a list of valid timezones: http://php.net/manual/en/timezones.php
 */
$config['timezone'] = 'Europe/Amsterdam';

/**
 * This password must be kept secret, and modified from the default value 123.
 * This password will give access to the installation page of simpleSAMLphp with
 * metadata listing and diagnostics pages.
 */
$config['auth.adminpassword'] = 'admin';

/**
 * This is a secret salt used by simpleSAMLphp when it needs to generate a secure hash
 * of a value. It must be changed from its default value to a secret value. The value of
 * 'secretsalt' can be any valid string of any length.
 *
 * A possible way to generate a random salt is by running the following command from a unix shell:
 * tr -c -d '0123456789abcdefghijklmnopqrstuvwxyz' </dev/urandom | dd bs=32 count=1 2>/dev/null;echo
 */
$config['secretsalt'] = 'defaultsecretsalt';

/*
 * Some information about the technical persons running this installation.
 * The email address will be used as the recipient address for error reports, and
 * also as the technical contact in generated metadata.
 */
$config['technicalcontact_name']  = '';
$config['technicalcontact_email'] = '';

/*
 * If you enable this option, simpleSAMLphp will log all sent and received messages
 * to the log file.
 *
 * This option also enables logging of the messages that are encrypted and decrypted.
 *
 * Note: The messages are logged with the DEBUG log level, so you also need to set
 * the 'logging.level' option to LOG_DEBUG.
 */
$config['debug'] = FALSE;

/**
 * Where the log file should be written to, defaults to serviceregistry/log/
 */
$config['loggingdir'] = 'log/';

/*
 * Logging.
 *
 * define the minimum log level to log
 *		LOG_ERR				No statistics, only errors
 *		LOG_WARNING			No statistics, only warnings/errors
 *		LOG_NOTICE			Statistics and errors
 *		LOG_INFO			Verbose logs
 *		LOG_DEBUG			Full debug logs - not reccomended for production
 *
 * Choose logging handler.
 *
 * Options: [syslog,file,errorlog]
 *
 */
$config['logging.level'] = LOG_NOTICE;
$config['logging.handler'] = 'syslog';