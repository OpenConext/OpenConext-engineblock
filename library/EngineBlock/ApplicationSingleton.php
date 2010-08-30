<?php

define('ENGINEBLOCK_FOLDER_LIBRARY'    , dirname(__FILE__) . '/../');
define('ENGINEBLOCK_FOLDER_APPLICATION', dirname(__FILE__) . '/../../application/');
define('ENGINEBLOCK_FOLDER_MODULES'    , ENGINEBLOCK_FOLDER_APPLICATION . 'modules/');

set_include_path(get_include_path() . PATH_SEPARATOR . ENGINEBLOCK_FOLDER_LIBRARY);

class EngineBlock_Exception extends Exception
{
}

class EngineBlock_ApplicationSingleton_BootstrapException extends Exception
{
}

class EngineBlock_ApplicationSingleton
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    protected static $s_instance;

    /**
     * @var string
     */
    protected $_applicationEnvironmentId;

    /**
     * @var EngineBlock_Http_Request
     */
    protected $_httpRequest;

    /**
     * @var EngineBlock_Http_Response
     */
    protected $_httpResponse;

    /**
     * @var array
     */
    protected $_configuration;

    /**
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * Get THE instance of the application singleton.
     *
     * @static
     * @return EngineBlock_ApplicationSingleton
     */
    public static function getInstance()
    {
        if (!isset(self::$s_instance)) {
            self::$s_instance = new self();
        }
        return self::$s_instance;
    }

    /**
     * Try to auto-load a class.
     *
     * Detects all modules and autoloads any class that begin with 'ModuleName_' and autoloads all EngineBlock_ classes.
     *
     * @static
     * @param string $className
     * @return bool Whether auto-loading worked
     */
    public static function autoLoad($className)
    {
        static $s_modules = array();
        static $s_libraries = array();

        // Find /modules/ directories
        if (empty($s_modules)) {
            $iterator = new DirectoryIterator(ENGINEBLOCK_FOLDER_MODULES);
            foreach ($iterator as $item) {
                if ($item->isDir() && !$item->isDot()) {
                    $s_modules[] = (string)$item;
                }
            }
        }

        $classNameParts = explode('_', $className);

        // EngineBlock class
        if ($classNameParts[0] === 'EngineBlock') {
            $fileName = implode('/', explode('_', $className)).'.php';
            $filePath = ENGINEBLOCK_FOLDER_LIBRARY . $fileName;

            if (!file_exists($filePath)) {
                return false;
            }

            include $filePath;

            return true;
        }

        // Module class?
        if (in_array($classNameParts[0], $s_modules)) {
            $fileName = implode('/', explode('_', $className)).'.php';

            if (!file_exists(ENGINEBLOCK_FOLDER_MODULES . $fileName)) {
                return false;
            }

            include ENGINEBLOCK_FOLDER_MODULES . $fileName;

            return true;
        }

        return false;
    }

    //////////// BOOTSTRAPPING

    /**
     * Bootstrap the application for a given environment id (like 'production').
     *
     * @param string $applicationEnvironmentId
     * @return EngineBlock_ApplicationSingleton Bootstrapped application singleton
     */
    public function bootstrap($applicationEnvironmentId)
    {
        $this->_applicationEnvironmentId = $applicationEnvironmentId;

        $this->bootstrapConfiguration();

        $this->bootstrapPhpSettings();

        $this->bootstrapAutoLoading();
        $this->bootstrapHttpCommunication();
        return $this;
    }

    protected function bootstrapAutoLoading()
    {
        spl_autoload_register(array($this, 'autoLoad'));
    }

    protected function bootstrapConfiguration()
    {
        $config = array();
        $configFilePath = ENGINEBLOCK_FOLDER_APPLICATION . 'configs/application.php';
        require $configFilePath;

        if (!isset($config[$this->_applicationEnvironmentId])) {
            $message = "No configuration in {$configFilePath} for application environment ID '{$this->_applicationEnvironmentId}'";
            throw new EngineBlock_ApplicationSingleton_BootstrapException($message);
        }

        $this->setConfiguration($config[$this->_applicationEnvironmentId]);
    }

    protected function bootstrapHttpCommunication()
    {
        $this->setHttpRequest(EngineBlock_Http_Request::createFromEnvironment());
        $this->setHttpResponse(new EngineBlock_Http_Response());
    }

    protected function bootstrapPhpSettings()
    {
        if (isset($this->_configuration['default_timezone'])) {
            date_default_timezone_set($this->_configuration['default_timezone']);
        }

        if (isset($this->_configuration['Php.DisplayErrors'])) {
            ini_set('display_errors', $this->_configuration['Php.DisplayErrors']);
        }

        if (isset($this->_configuration['Php.ErrorReporting'])) {
            error_reporting($this->_configuration['Php.ErrorReporting']);
        }
    }

    //////////// CONFIGURATION

    public function getConfiguration()
    {
        return $this->_configuration;
    }

    public function getConfigurationValue($key, $default = null)
    {
        if (isset($this->_configuration[$key])) {
            return $this->_configuration[$key];
        }

        return $default;
    }

    public function getConfigurationValuesForPrefix($keyPrefix)
    {
        $values = array();
        $keys = array_keys($this->_configuration);
        foreach ($keys as $key) {
            if (strpos($key, $keyPrefix) === 0) {
                $values[$key] = $this->_configuration[$key];
            }
        }
        return $values;
    }

    public function setConfiguration($applicationConfiguration)
    {
        $this->_configuration = $applicationConfiguration;
        return $this;
    }

    //////////// HTTP COMMUNICATION

    /**
     * @return EngineBlock_Http_Request
     */
    public function getHttpRequest()
    {
        return $this->_httpRequest;
    }

    public function setHttpRequest($request)
    {
        $this->_httpRequest = $request;
        return $this;
    }

    /**
     * @return EngineBlock_Http_Response
     */
    public function getHttpResponse()
    {
        return $this->_httpResponse;
    }

    public function setHttpResponse($response)
    {
        $this->_httpResponse = $response;
        return $this;
    }
}