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
    const DEFAULT_APPLICATION_CONFIGFILE = 'configs/application.ini';

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
     * @var Zend_Config
     */
    protected $_configuration;

    /**
     * @var Zend_Log
     */
    protected $_log;

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

        // Known libraries (like Zend and EngineBlock)
        if (in_array($classNameParts[0], array('EngineBlock', 'Zend'))) {
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
     * Note that the order or bootstrapping is very important.
     *
     * @param string $applicationEnvironmentId
     * @return EngineBlock_ApplicationSingleton Bootstrapped application singleton
     */
    public function bootstrap($applicationEnvironmentId, $configurationFile = "")
    {
        $this->_applicationEnvironmentId = $applicationEnvironmentId;

        $this->_bootstrapAutoLoading();

        $this->_bootstrapConfiguration($configurationFile);

        $this->_bootstrapPhpSettings();
        $this->_bootstrapErrorReporting();
        $this->_bootstrapLogging();
        $this->_bootstrapHttpCommunication();

        return $this;
    }

    protected function _bootstrapAutoLoading()
    {
        if(!function_exists('spl_autoload_register')) {
            throw new EngineBlock_Exception('SPL Autoload not available! Please use PHP > v5.1.2');
        }
        spl_autoload_register(array($this, 'autoLoad'));
    }

    protected function _bootstrapConfiguration($configFile)
    {
        if (!$configFile) {
            $configFile = ENGINEBLOCK_FOLDER_APPLICATION . self::DEFAULT_APPLICATION_CONFIGFILE;
        }
        if (!file_exists($configFile)) {
            throw new EngineBlock_Exception("Configuration file '$configFile does not exist!'");
        }

        $env = $this->_applicationEnvironmentId;
        $configuration = $this->_getConfigurationLoader($configFile)->$env;
        $this->setConfiguration($configuration);
    }

    protected function _getConfigurationLoader($environmentId)
    {
        return new Zend_Config_Ini($environmentId);
    }

    protected function _bootstrapLogging()
    {
        if (!isset($this->_configuration->logs)) {
            throw new EngineBlock_Exception("No logs defined! Logging is required, please set logs. in your application.ini");
        }
        
        $this->_log = Zend_Log::factory($this->_configuration->logs);
    }

    protected function _bootstrapHttpCommunication()
    {
        $this->setHttpRequest(EngineBlock_Http_Request::createFromEnvironment());
        $this->setHttpResponse(new EngineBlock_Http_Response());
    }

    protected function _bootstrapPhpSettings()
    {
        $settings = $this->_configuration->phpSettings->toArray();
        $this->_setIniSettings($settings);
    }

    protected function _setIniSettings($settings, $prefix = '')
    {
        foreach ($settings as $settingName => $settingValue) {
            if (is_array($settingValue)) {
                $this->_setIniSettings((array)$settingValue, $prefix . $settingName . '.');
            }
            else {
                ini_set($prefix . $settingName, $settingValue);
            }
        }
    }

    protected function _bootstrapErrorReporting()
    {
        register_shutdown_function(array($this, 'handleShutdown'));
        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'handleException'));
    }

    public function handleException(Exception $e)
    {
        $this->reportError($e);

        $message = 'A exceptional condition occurred, it has been logged and sent to the administrator.';
        if ($this->getConfiguration()->debug) {
            $message .= PHP_EOL . '<br /><br /> ERROR: ' . PHP_EOL;
            $message .= '<br /><strong style="color: red"><pre>' . var_export($e, true) . '</pre></strong>';
        }
        die($message);
    }

    public function handleError($errorNumber, $errorMesage, $errorFile, $errorLine)
    {
        if (!(error_reporting() & $errorNumber)) {
            // This error code is not included in error_reporting
            return;
        }

        $this->reportError(new Exception($errorMesage . " [$errorFile:$errorLine]", $errorNumber));

        /* Execute PHP internal error handler */
        return false;
    }

    public function handleShutdown()
    {
        $lastError = error_get_last();
        if($lastError['type'] !== E_ERROR && $lastError['type'] !== E_USER_ERROR) {
            // Not a fatal error, probably a shutdown
            return false;
        }

        $this->reportError(new Exception('Fatal error: ' . var_export($lastError, true)));

        $message = 'A error occurred, it has been logged and sent to the administrator.';
        if ($this->getConfiguration()->debug) {
            $message .= PHP_EOL . '<br /><br /> ERROR: ' . PHP_EOL;
            $message .= '<br /><strong style="color: red"><pre>' . var_export($lastError, true) . '</pre></strong>';
        }
        die($message);
    }

    public function reportError(Exception $e)
    {
        if (isset($this->getConfiguration()->error->reports)) {
            $reportingConfiguration = $this->getConfiguration()->error->reports;
            $reporter = new EngineBlock_Error_Reporter($reportingConfiguration);
            $reporter->report($e);
        }
    }

    //////////// CONFIGURATION

    public function getApplicationEnvironmentId()
    {
        return $this->_applicationEnvironmentId;
    }

    /**
     * @return Zend_Config
     */
    public function getConfiguration()
    {
        return $this->_configuration;
    }

    public function getConfigurationValue($key, $default = null)
    {
        if (isset($this->_configuration->$key)) {
            return $this->_configuration->$key;
        }

        return $default;
    }

    public function setConfiguration(Zend_Config $applicationConfiguration)
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

    //////////// LOGGING

    /**
     * @return Zend_Log
     */
    public function getLog()
    {
        return $this->_log;
    }
}

/**
 * @return Zend_Log
 */
function ebLog()
{
    return EngineBlock_ApplicationSingleton::getInstance()->getLog();
}
