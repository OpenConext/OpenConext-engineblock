# Upgrading to Symfony 4

This document outlines the steps taken to upgrade OpenConext EngineBlock from Symfony 3.4 to Symfony 4.4.

## Changes Made

1. Updated composer.json:
   - Added symfony/flex
   - Replaced symfony/symfony with individual Symfony 4.4 components
   - Updated dependencies to be compatible with Symfony 4
   - Updated composer scripts and directory configuration

2. Created new directory structure:
   - src/Kernel.php (replaces app/AppKernel.php)
   - config/bundles.php (replaces registerBundles() in AppKernel)
   - config/packages/* (configuration files for each bundle)
   - config/services.yaml (service configuration)
   - config/routes.yaml (route configuration)
   - public/index.php (replaces web/app.php)
   - bin/console (replaces app/console)
   - .env (environment variables)

3. Created Symfony 4 configuration files:
   - framework.yaml
   - doctrine.yaml
   - security.yaml
   - twig.yaml
   - monolog.yaml
   - swiftmailer.yaml
   - doctrine_migrations.yaml

## Next Steps

To complete the upgrade, follow these steps:

1. Run composer update to install the new dependencies:
   ```
   composer update
   ```

2. Clear the cache:
   ```
   bin/console cache:clear
   ```

3. Test the application to ensure it works correctly.

4. Update your web server configuration to point to the new public directory instead of web.

5. Gradually migrate the remaining configuration from app/config to config/packages.

6. Update any hardcoded paths in your code that reference the old directory structure.

7. Update any custom services to use the new autowiring and autoconfiguration features.

## Backward Compatibility

The upgrade has been designed to maintain backward compatibility as much as possible:

- The Kernel is configured to load both the new Symfony 4 configuration and the legacy configuration from app/config.
- Routes are imported from the legacy routing.yml file.
- The old directory structure (app/, web/) is still supported but deprecated.

## Known Issues

- Some services may need to be manually configured if they rely on the old service container.
- Custom bundles may need to be updated to be compatible with Symfony 4.
- The application may need additional testing to ensure all features work correctly.

## References

- [Symfony 4.4 Documentation](https://symfony.com/doc/4.4)
- [Upgrading from Symfony 3 to 4](https://symfony.com/doc/4.4/setup/upgrade_major.html)
- [Symfony Flex](https://symfony.com/doc/4.4/setup/flex.html)
