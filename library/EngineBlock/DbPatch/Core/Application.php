<?php

/**
 * Override of DbPatch Application class to inject configuration, read from the EngineBlock configuration file
 *
 * @throws EngineBlock_Exception
 *
 */
class EngineBlock_DbPatch_Core_Application extends DbPatch_Core_Application
{
    protected function getConfig($filename = null)
    {
        require_once dirname(__FILE__) . '/../../../../app/bootstrap.php.cache';
        require_once dirname(__FILE__) . '/../../../../app/AppKernel.php';

        $symfonyEnvironment = getenv('SYMFONY_ENV') ?: 'prod';
        $kernel = new AppKernel($symfonyEnvironment, false);
        $kernel->boot();

        try {
            $engineBlock = EngineBlock_ApplicationSingleton::getInstance();

            $databaseConfig = $engineBlock->getConfiguration()->database;

            $config = array(
                'db' => array(
                    'adapter'   => 'MySqli',
                    'params' => array(
                        'host'      => $databaseConfig->host,
                        'username'  => $databaseConfig->user,
                        'password'  => $databaseConfig->password,
                        'dbname'    => $databaseConfig->dbname,
                        'charset'   => 'utf8',
                    ),
                ),
                'patch_directory' => realpath(__DIR__ . '/../../../../database/patch'),
                'color' => true,
            );
            return new Zend_Config($config, true);
        } catch (Exception $e) {
           die($e->getMessage()."\n");
        }
    }
}
