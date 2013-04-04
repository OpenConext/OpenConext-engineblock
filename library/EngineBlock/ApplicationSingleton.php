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

require_once ENGINEBLOCK_FOLDER_ROOT . 'vendor/lucasvanlierop/php-profiler/lib/Profiler.php';
\Lvl\Profiler::markBootstrapStart();

define('ENGINEBLOCK_FOLDER_LIBRARY'    , ENGINEBLOCK_FOLDER_ROOT . 'library/');
define('ENGINEBLOCK_FOLDER_APPLICATION', ENGINEBLOCK_FOLDER_ROOT . 'application/');
define('ENGINEBLOCK_FOLDER_MODULES'    , ENGINEBLOCK_FOLDER_APPLICATION . 'modules/');

define('LIBRARY_PATH', ENGINEBLOCK_FOLDER_LIBRARY);

set_include_path(ENGINEBLOCK_FOLDER_LIBRARY . PATH_SEPARATOR . get_include_path());

require __DIR__ . '/Exception.php';
require __DIR__ . '/Application/Bootstrapper.php';

class EngineBlock_ApplicationSingleton
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    protected static $s_instance;

    /**
     * @var string
     */
    protected $_environmentId;

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

    /**
     * @var Zend_Layout
     */
    protected $_layout;

    /**
     * @var EngineBlock_Application_Bootstrapper
     */
    protected $_bootstrapper;

    /**
     * @var EngineBlock_Application_ErrorHandler
     */
    protected $_errorHandler;

    /**
     * @var EngineBlock_Application_DiContainer
     */
    protected $_diContainer;


    /**
     * @var \Lvl\Profiler
     */
    protected $profiler;

    /**
     *
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
     * Get THE Log instance.
     *
     * @static
     * @return EngineBlock_Log
     */
    public static function getLog()
    {
        return self::getInstance()->getLogInstance();
    }

    /**
     *
     */
    public function bootstrap()
    {
        if (!isset($this->_bootstrapper)) {
            $this->_bootstrapper = new EngineBlock_Application_Bootstrapper($this);
        }
        $this->_bootstrapper->bootstrap();
    }

    /**
     * @param Exception $exception
     * @return bool
     */
    public function reportError(Exception $exception)
    {
        $log = $this->getLogInstance();
        if (!$log) {
            return false;
        }

        if ($exception instanceof EngineBlock_Exception) {
            $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::createFromException($exception);
            $severity = $exception->getSeverity();
        } else {
            $additionalInfo = null;
            $severity = EngineBlock_Log::ERR;
        }

        $log->attach($exception->getTraceAsString(), 'trace');

        // attach previous exceptions
        $prevException = $exception;
        while ($prevException = $prevException->getPrevious()) {
            $log->attach($prevException, 'previous exception');
        }

        $message = $exception->getMessage();
        if (empty($message)) {
            $message = 'Exception without message "' . get_class($exception) . '"';
        }

        // log exception
        $log->log(
            $message,
            $severity,
            $additionalInfo
        );

        // flush all messages in queue, something went wrong!
        $log->getQueueWriter()->flush('error caught');

        return true;
    }

    /**
     * @return Zend_Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }

    /**
     * @param Zend_Layout $layout
     * @return EngineBlock_ApplicationSingleton
     */
    public function setLayout(Zend_Layout $layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    /**
     * @return Zend_Translate
     */
    public function getTranslator()
    {
        return $this->_translator;
    }

    /**
     * @param Zend_Translate $translator
     * @return EngineBlock_ApplicationSingleton
     */
    public function setTranslator(Zend_Translate $translator)
    {
        $this->_translator = $translator;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironmentId()
    {
        return $this->_environmentId;
    }

    /**
     * @param $environmentId
     * @return EngineBlock_ApplicationSingleton
     */
    public function setEnvironmentId($environmentId)
    {
        $this->_environmentId = $environmentId;
        return $this;
    }

    /**
     * @return Zend_Config
     */
    public function getConfiguration()
    {
        return $this->_configuration;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getConfigurationValue($key, $default = null)
    {
        if (isset($this->_configuration->$key)) {
            return $this->_configuration->$key;
        }

        return $default;
    }

    /**
     * @param Zend_Config $applicationConfiguration
     * @return EngineBlock_ApplicationSingleton
     */
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

    /**
     * @param $request
     * @return EngineBlock_ApplicationSingleton
     */
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

    /**
     * @param $response
     * @return EngineBlock_ApplicationSingleton
     */
    public function setHttpResponse($response)
    {
        $this->_httpResponse = $response;
        return $this;
    }

    //////////// LOGGING

    /**
     * @return EngineBlock_Log
     */
    public function getLogInstance()
    {
        return $this->_log;
    }

    /**
     * @param Zend_Log $log
     * @return EngineBlock_ApplicationSingleton
     */
    public function setLogInstance(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }

    public function getErrorHandler()
    {
        return $this->_errorHandler;
    }

    public function setErrorHandler(EngineBlock_Application_ErrorHandler $errorHandler)
    {
        $this->_errorHandler = $errorHandler;
        return $this;
    }

    /**
     * @param \EngineBlock_Application_DiContainer $diContainer
     */
    public function setDiContainer(\EngineBlock_Application_DiContainer $diContainer)
    {
        $this->_diContainer = $diContainer;
        return $this;
    }

    /**
     * @return \EngineBlock_Application_DiContainer
     */
    public function getDiContainer()
    {
        return $this->_diContainer;
    }

    /**
     * @param \\Lvl\Profiler $profiler
     */
    public function setProfiler(\Lvl\Profiler $profiler)
    {
        $this->profiler = $profiler;
        return $this;
    }

    /**
     * @return \\Lvl\Profiler
     */
    public function getProfiler()
    {
        return $this->profiler;
    }
}
