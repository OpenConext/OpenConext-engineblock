<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

define('ENGINEBLOCK_FOLDER_ROOT'       , dirname(__FILE__) . '/../../');
define('ENGINEBLOCK_FOLDER_LIBRARY'    , ENGINEBLOCK_FOLDER_ROOT . 'library/');
define('ENGINEBLOCK_FOLDER_APPLICATION', ENGINEBLOCK_FOLDER_ROOT . 'application/');
define('ENGINEBLOCK_FOLDER_MODULES'    , ENGINEBLOCK_FOLDER_APPLICATION . 'modules/');

set_include_path(ENGINEBLOCK_FOLDER_LIBRARY . PATH_SEPARATOR . get_include_path());

class EngineBlock_Exception extends Exception
{
}

class EngineBlock_ApplicationSingleton_BootstrapException extends Exception
{
}

class EngineBlock_ApplicationSingleton
{
    const CONFIG_FILE_DEFAULT = 'configs/application.ini';
    const CONFIG_FILE_ENVIORNMENT = '/etc/surfconext/engineblock.ini';

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
     * @var Zend_Translate
     */
    protected $_translator;

    protected $_layout;

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
        if (in_array($classNameParts[0], array('Grouper', 'EngineBlock', 'OpenSocial', 'Zend'))) {
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
     * Bootstrap the application.
     *
     * Note that the order or bootstrapping is very important.
     *
     * @return EngineBlock_ApplicationSingleton Bootstrapped application singleton
     */
    public function bootstrap()
    {
        $this->_bootstrapAutoLoading();

        $this->_setEnvironmentIdByEnvironment();

        $this->_bootstrapConfiguration();

        $this->_setEnvironmentIdByDetection();

        $this->_bootstrapEnvironmentConfiguration();

        $this->_bootstrapPhpSettings();
        $this->_bootstrapErrorReporting();
        $this->_bootstrapLogging();
        $this->_bootstrapHttpCommunication();

        $this->_bootstrapLayout();
        $this->_bootstrapTranslations();

        return $this;
    }

    protected function _bootstrapAutoLoading()
    {
        if(!function_exists('spl_autoload_register')) {
            throw new EngineBlock_Exception('SPL Autoload not available! Please use PHP > v5.1.2');
        }
        spl_autoload_register(array($this, 'autoLoad'));
    }

    protected function _setEnvironmentIdByEnvironment()
    {
        // Get from predefined constant
        if (defined('ENGINEBLOCK_ENV')) {
            $this->_applicationEnvironmentId = ENGINEBLOCK_ENV;
        }
        // Get from environment variable (from Apache or the shell)
        else if (getenv('ENGINEBLOCK_ENV')) {
            $this->_applicationEnvironmentId = getenv('ENGINEBLOCK_ENV');
            define('ENGINEBLOCK_ENV', $this->_applicationEnvironmentId);
        }
    }

    protected function _bootstrapConfiguration()
    {
        $configContent = $this->_getAllConfigsContent();
        $this->setConfiguration($this->_getConfigurationLoader($configContent));
    }

    /**
     * Concatenate the INI files from /application/configs/application.ini
     * and from /etc/surfconext/engineblock.ini and return the result as a string.
     *
     * @return string
     */
    protected function _getAllConfigsContent()
    {
        $configFiles = array(
            ENGINEBLOCK_FOLDER_APPLICATION . self::CONFIG_FILE_DEFAULT,
            self::CONFIG_FILE_ENVIORNMENT,
        );

        $configFileContents = "";
        foreach ($configFiles as $configFile) {
            $configFileContents .= file_get_contents($configFile) . PHP_EOL;
        }
        return $configFileContents;
    }

    protected function _getConfigurationLoader($configContent)
    {
        return new EngineBlock_Config_Ini($configContent);
    }

    protected function _setEnvironmentIdByDetection()
    {
        if (isset($this->_applicationEnvironmentId)) {
            // Detection not required.
            return;
        }

        foreach ($this->_configuration as $environmentId => $environmentConfiguration) {
            if (!isset($environmentConfiguration->env)) {
                continue;
            }

            $environmentMatches = false;
            if (isset($environmentConfiguration->env->host)) {
                if (gethostname()===$environmentConfiguration->env->host) {
                    $environmentMatches = true;
                }
                else {
                    continue;
                }
            }

            if (isset($environmentConfiguration->env->path)) {
                if (strpos(__DIR__, $environmentConfiguration->env->path)===0) {
                    $environmentMatches = true;
                }
                else {
                    continue;
                }
            }
            if ($environmentMatches) {
                $this->_applicationEnvironmentId = $environmentId;
                define('ENGINEBLOCK_ENV', $environmentId);
                return;
            }
        }

        throw new EngineBlock_ApplicationSingleton_BootstrapException('Unable to detect an environment!');
    }

    protected function _bootstrapEnvironmentConfiguration()
    {
        $env = $this->_applicationEnvironmentId;
        if (!isset($this->_configuration->$env)) {
            throw new EngineBlock_ApplicationSingleton_BootstrapException("Environment '$env' does not exist?!?");
        }
        $this->_configuration = $this->_configuration->$env;
    }

    protected function _bootstrapLogging()
    {
        if (!isset($this->_configuration->logs)) {
            throw new EngineBlock_Exception("No logs defined! Logging is required, please set logs. in your application.ini");
        }
        
        $this->_log = EngineBlock_Log::factory($this->_configuration->logs);
    }

    protected function _bootstrapHttpCommunication()
    {
        $this->_httpRequest = EngineBlock_Http_Request::createFromEnvironment();

        $response = new EngineBlock_Http_Response();
        $response->setHeader('Strict-Transport-Security', 'max-age=15768000; includeSubDomains');
        $this->_httpResponse = $response;
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

    protected function _bootstrapLayout()
    {
        $layout = new Zend_Layout();

        // Set a layout script path:
        $layout->setLayoutPath(ENGINEBLOCK_FOLDER_APPLICATION . 'layouts/scripts/');

        // Defaults
        $defaultsConfig = $this->_configuration->defaults;
        $layout->title  = $defaultsConfig->title;
        $layout->header = $defaultsConfig->header;

        // choose a different layout script:
        $layout->setLayout($defaultsConfig->layout);

        $this->_layout = $layout;
    }

    public function getLayout()
    {
        return $this->_layout;
    }

    protected function _bootstrapTranslations()
    {
        $translate = new Zend_Translate(
            'Array',
            ENGINEBLOCK_FOLDER_ROOT . '/languages/en.php',
            'en'
        );

        $translate->addTranslation(
            array(
                'content' => ENGINEBLOCK_FOLDER_ROOT . '/languages/nl.php',
                'locale'  => 'nl'
            )
        );

        // If the URL has &lang=nl in it or the lang var is posted, or a lang cookie was set, then use that locale
        $cookieLang = $this->_httpRequest->getCookie('lang');
        $getLang = $this->_httpRequest->getQueryParameter('lang');
        $postLang = $this->_httpRequest->getPostParameter('lang');

        $lang = null;
        if ($getLang) {
            $lang = strtolower($getLang);
        } else if ($postLang) {
            $lang = strtolower($postLang);
        } else {
            $lang = strtolower($cookieLang);
        }

        $langCookieConfig = $this->getConfigurationValue('cookie')->lang;
        $cookieDomain = $langCookieConfig->domain;
        $cookieExpiry = null;
        if (isset($langCookieConfig->expiry) && $langCookieConfig->expiry > 0) {
            $cookieExpiry =  time() + $langCookieConfig->expiry;
        }

        if ($lang && $translate->getAdapter()->isAvailable($lang)) {
            $translate->setLocale($lang);
            $this->_httpResponse->setCookie('lang', $lang, $cookieExpiry, '/', $cookieDomain);
        }
        else {
            $translate->setLocale('en');
            $this->_httpResponse->setCookie('lang', 'en', $cookieExpiry, '/', $cookieDomain);
        }

        $this->_translator = $translate;
    }

    public function getTranslator()
    {
        return $this->_translator;
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
            // Execute PHP internal error handler
            return false;
        }

        $this->reportError(new Exception($errorMesage . " [$errorFile:$errorLine]", $errorNumber));

        // Execute PHP internal error handler
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

    public function reportError(Exception $exception)
    {
        $log = ebLog();
        if (!$log) {
            return false;
        }

        $log->err($exception->getMessage());
        $log->debug($exception->getTraceAsString());
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
     * @return EngineBlock_Log
     */
    public function getLog()
    {
        return $this->_log;
    }
}

/**
 * @return EngineBlock_Log
 */
function ebLog()
{
    return EngineBlock_ApplicationSingleton::getInstance()->getLog();
}
