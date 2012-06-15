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
        $engineBlock = EngineBlock_ApplicationSingleton::getInstance();
        $engineBlock->bootstrap();

        $ebConfig = $engineBlock->getConfiguration();
        $masterDbConfigName = $ebConfig->database->masters->get(0);
        $dbConfig           = $ebConfig->database->get($masterDbConfigName);
        $dsnParsed = parse_url($dbConfig->dsn);
        $dsnPathParts = explode(';', $dsnParsed['path']);
        $dsnProperties = array();
        foreach ($dsnPathParts as $dsnPathPart) {
            $dsnPathPart = explode('=', $dsnPathPart);
            $dsnProperties[array_shift($dsnPathPart)] = implode($dsnPathPart, '=');
        }

        $config = array(
            'db' => array(
                'adapter'   => $this->_convertPdoDriverToZendDbAdapter($dsnParsed['scheme']),
                'params' => array(
                    'host'      => $dsnProperties['host'],
                    'username'  => $dbConfig->user,
                    'password'  => $dbConfig->password,
                    'dbname'    => $dsnProperties['dbname'],
                ),
            ),
            'patch_directory' => realpath(__DIR__ . '/../../../../database/patch'),
            'color' => true,
        );
        return new Zend_Config($config, true);
    }

    private function _convertPdoDriverToZendDbAdapter($pdoDriver)
    {
        switch ($pdoDriver) {
            case 'mysql':
                return 'Mysqli';
            default:
                throw new EngineBlock_Exception("Unsupported PDO driver '$pdoDriver'");
        }
    }
}