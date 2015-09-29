<?php

define('ENGINEBLOCK_FOLDER_ROOT'       , realpath(__DIR__ . '/../../') . '/');
define('ENGINEBLOCK_FOLDER_LIBRARY'    , ENGINEBLOCK_FOLDER_ROOT . 'library/');
define('ENGINEBLOCK_FOLDER_APPLICATION', ENGINEBLOCK_FOLDER_ROOT . 'application/');
define('ENGINEBLOCK_FOLDER_MODULES'    , ENGINEBLOCK_FOLDER_APPLICATION . 'modules/');
define('ENGINEBLOCK_FOLDER_VENDOR'    , ENGINEBLOCK_FOLDER_ROOT . 'vendor/');

require_once ENGINEBLOCK_FOLDER_VENDOR . 'autoload.php';

// @todo this only necessary for code which bypasses autoloading like Zend_Translate
$includePath = get_include_path();
$includePath = ENGINEBLOCK_FOLDER_VENDOR .  'zendframework/zendframework1/library' . PATH_SEPARATOR . $includePath;
set_include_path($includePath);

class EngineBlock_ApplicationSingleton
{
    /**
     * Special fake IP address to use when we're running on the CLI.
     */
    const IP_ADDRESS_CLI = '127.0.0.235';

    /**
     * @var EngineBlock_ApplicationSingleton
     */
    protected static $s_instance;

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
    protected $_configuration = null;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $_log;

    /**
     * The ID that allows one to track all log messages that belong together.
     *
     * @var string|null
     */
    protected $_logRequestId;

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
     * @return Psr\Log\LoggerInterface
     */
    public static function getLog()
    {
        return self::getInstance()->getLogInstance();
    }

    /**
     * Flushes any currently buffered log messages and causes any subsequent messages to be directly written to their
     * destination.
     *
     * @param string $reason The message that will be logged after the currently buffered messages have been flushed.
     */
    public function flushLog($reason)
    {
        $activationStrategy = EngineBlock_Log_Monolog_Handler_FingersCrossed_ManualOrErrorLevelActivationStrategyFactory::getManufacturedStrategy();
        $logger = $this->getLog();

        if ($activationStrategy) {
            $activationStrategy->activate();
            $logger->notice($reason);
        } elseif ($this->getConfiguration()->debug) {
            $logger->notice(sprintf("No log buffer to flush. Reason given for flushing: '%s'.", $reason));
        } else {
            $logger->warning(sprintf("Unable to flush the log buffer. Reason given for flushing: '%s'.", $reason));
        }
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
     * @param string $messageSuffix
     * @return bool
     */
    public function reportError(Exception $exception, $messageSuffix = '')
    {
        $log = $this->getLogInstance();
        if (!$log) {
            return false;
        }

        $logContext = array('exception' => $exception);

        if ($exception instanceof EngineBlock_Exception) {
            $severity = $exception->getSeverity();
        } else {
            $severity = EngineBlock_Exception::CODE_ERROR;
        }

        // add previous exceptions to log context
        $prevException = $exception;
        while ($prevException = $prevException->getPrevious()) {
            if (!isset($logContext['previous_exceptions'])) {
                $logContext['previous_exceptions'] = array();
            }

            $logContext['previous_exceptions'][] = (string) $prevException;
        }

        $message = $exception->getMessage();
        if (empty($message)) {
            $message = 'Exception without message "' . get_class($exception) . '"';
        }

        if ($messageSuffix) {
            $message .= ' | ' . $messageSuffix;
        }

        $log->log($severity, $message, $logContext);

        // Store some valuable debug info in session so it can be displayed on feedback pages
        $_SESSION['feedbackInfo'] = $this->collectFeedbackInfo();

        // flush all messages in queue, something went wrong!
        $this->flushLog('An error was caught');

        return true;
    }

    /**
     * @return array
     */
    private function collectFeedbackInfo()
    {
        $feedbackInfo = array();
        $feedbackInfo['timestamp'] = date('c');
        $feedbackInfo['requestId'] = $this->getLogRequestId() ?: 'N/A';
        $feedbackInfo['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
        $feedbackInfo['ipAddress'] = $this->getClientIpAddress();

        // @todo  reset this when login is succesful
        // Find the current identity provider
        if (isset($_SESSION['currentServiceProvider'])) {
            $feedbackInfo['serviceProvider'] = $_SESSION['currentServiceProvider'];
        }

        // @todo  reset this when login is succesful
        // Find the current identity provider
        if (isset($_SESSION['currentIdentityProvider'])) {
            $feedbackInfo['identityProvider'] = $_SESSION['currentIdentityProvider'];
        }

        return $feedbackInfo;
    }

    /**
     * Get the IP address for the HTTP client (optionally taking into account proxies).
     *
     * See also: http://stackoverflow.com/a/7623231/4512
     *
     * @return string
     * @throws EngineBlock_Exception
     */
    public function getClientIpAddress()
    {
        $trustedProxyIpAddresses = $this->getConfiguration()->get('trustedProxyIps');

        if ($trustedProxyIpAddresses instanceof Zend_Config) {
            $trustedProxyIpAddresses = $trustedProxyIpAddresses->toArray();
        }
        if (!$trustedProxyIpAddresses) {
            $trustedProxyIpAddresses = array();
        }
        if (!is_array($trustedProxyIpAddresses)) {
            throw new EngineBlock_Exception('Trusted IP addresses is not an array: ' . print_r($trustedProxyIpAddresses, true));
        }

        $hasForwardedFor = isset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $hasClientIp     = isset($_SERVER['HTTP_CLIENT_IP']);
        $hasRemoteAddr   = isset($_SERVER['REMOTE_ADDR']);
        $isRemoteAddrTrusted = $hasRemoteAddr && in_array($_SERVER['REMOTE_ADDR'], $trustedProxyIpAddresses);

        if ($hasForwardedFor AND $hasRemoteAddr AND $isRemoteAddrTrusted) {
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            // Format: "X-Forwarded-For: client1, proxy1, proxy2"
            $client_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return array_shift($client_ips);
        }

        if ($hasClientIp AND $hasRemoteAddr AND $isRemoteAddrTrusted)
        {
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            $client_ips = explode(',', $_SERVER['HTTP_CLIENT_IP']);

            return array_shift($client_ips);
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        if (php_sapi_name() == "cli") {
            return self::IP_ADDRESS_CLI;
        }

        throw new EngineBlock_Exception('Unable to determine IP address!');
    }

    /**
     * Get the hostname that EngineBlock is hosted on and should use in absolute URLs.
     *
     * @return string
     * @throws EngineBlock_Exception
     */
    public function getHostname()
    {
        $configHostname = $this->getConfiguration()->get('hostname');

        if (is_string($configHostname) && !empty($configHostname)) {
            return $configHostname;
        }

        $httpRequestHostname = $_SERVER['HTTP_HOST'];
        $validator = new Zend_Validate_Hostname();
        if ($validator->isValid($httpRequestHostname)) {
            return $httpRequestHostname;
        }

        throw new EngineBlock_Exception('No hostname configured and Host header is invalid.');
    }

    /**
     * Logs exception and redirects user to feedback page
     *
     * @param Exception $exception
     * @param string $feedbackUrl Url to which the user will be redirected
     * @param array $feedbackInfo Optional feedback info in name/value format which will be shown on feedback page
     */
    public function handleExceptionWithFeedback(
        Exception $exception,
        $feedbackUrl,
        $feedbackInfo = array()
    )
    {
        $messageSuffix = '-> Redirecting to feedback page';
        $this->reportError($exception, $messageSuffix);
        $_SESSION['feedbackInfo'] = array_merge($feedbackInfo, $_SESSION['feedbackInfo']);
        $this->getHttpResponse()->setRedirectUrl($feedbackUrl);
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
     * @return Psr\Log\LoggerInterface
     */
    public function getLogInstance()
    {
        return $this->_log;
    }

    /**
     * @param Psr\Log\LoggerInterface $log
     * @return EngineBlock_ApplicationSingleton
     */
    public function setLogInstance(Psr\Log\LoggerInterface $log)
    {
        $this->_log = $log;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogRequestId()
    {
        return $this->_logRequestId;
    }

    /**
     * @param string $id
     * @return EngineBlock_ApplicationSingleton
     */
    public function setLogRequestId($id)
    {
        if (!is_string($id)) {
            throw new InvalidArgumentException(
                sprintf("Invalid log request ID specified: expected string, but got '%s'", gettype($id))
            );
        }

        $this->_logRequestId = $id;
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
}
