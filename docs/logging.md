# Logging

## Overview

The default logging method of Engineblock is logging to `stderr`, which is particularly useful for Docker-based deployments.
However, logging to `syslog` will still be possible for environments where traditional syslog logging is required.

## Configuring Logging to Syslog

To configure `logging.yml` to log to syslog, you should use the syslog handler in the logging configuration.
Below is an example configuration:

```yaml
monolog:
    channels: ["%logger.channel%", "authentication"]
    handlers:
        main:
            type: fingers_crossed
            activation_strategy: engineblock.logger.manual_or_error_activation_strategy
            passthru_level: "%logger.fingers_crossed.passthru_level%"
            channels: [!authentication]
            handler: nested
        authentication:
            type:      syslog
            ident:     EBAUTH
            facility:  user
            level:     INFO
            channels:  [authentication]
            formatter: engineblock.logger.formatter.syslog_json
        nested:
            type: syslog
            ident: "EBLOG"
            formatter: engineblock.logger.formatter.syslog_json`
        console:
            type: console
            process_psr_3_messages: false
            channels: [!event, !doctrine, !console]
