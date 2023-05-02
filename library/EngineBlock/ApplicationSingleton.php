<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Logger\Handler\FingersCrossed\ManualOrDecoratedActivationStrategy;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\EntityNotFoundException;
use OpenConext\EngineBlock\Request\RequestId;
use OpenConext\EngineBlockBundle\Exception\Art;
use Psr\Log\LoggerInterface;
use SAML2\XML\saml\Issuer;
use Symfony\Component\DependencyInjection\ContainerInterface;

define('ENGINEBLOCK_FOLDER_ROOT'       , realpath(__DIR__ . '/../../') . '/');
define('ENGINEBLOCK_FOLDER_LIBRARY'    , ENGINEBLOCK_FOLDER_ROOT . 'library/');
define('ENGINEBLOCK_FOLDER_APPLICATION', ENGINEBLOCK_FOLDER_ROOT . 'application/');
define('ENGINEBLOCK_FOLDER_MODULES'    , ENGINEBLOCK_FOLDER_ROOT . 'app/Resources/views/modules/');
define('ENGINEBLOCK_FOLDER_VENDOR'    , ENGINEBLOCK_FOLDER_ROOT . 'vendor/');

require_once ENGINEBLOCK_FOLDER_VENDOR . 'autoload.php';

class EngineBlock_ApplicationSingleton
{
    /**
     * Special fake IP address to use when we're running on the CLI.
     */
    const IP_ADDRESS_CLI = '127.0.0.235';

    /**
     * @var null|string
     */
    public $authenticationStateSpEntityId;

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
     * @var Psr\Log\LoggerInterface
     */
    protected $_log;

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
     * @var ManualOrDecoratedActivationStrategy
     */
    private $_activationStrategy;

    /**
     * @var null|RequestId
     */
    private $_requestId;

    private function __construct()
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
        $logger = $this->getLog();

        if ($this->_activationStrategy) {
            $this->_activationStrategy->activate();
            $logger->notice($reason);
        } else {
            $logger->warning(sprintf("Unable to flush the log buffer. Reason given for flushing: '%s'.", $reason));
        }
    }

    /**
     * @param LoggerInterface                     $logger
     * @param ManualOrDecoratedActivationStrategy $activationStrategy
     * @param RequestId                           $requestId
     * @param ContainerInterface                  $container
     */
    public function bootstrap(
        LoggerInterface $logger,
        ManualOrDecoratedActivationStrategy $activationStrategy,
        RequestId $requestId,
        ContainerInterface $container
    ) {
        $this->setLogInstance($logger);
        $this->_activationStrategy = $activationStrategy;
        $this->_requestId = $requestId;

        // Load the legacy DI container. There are three flavours:
        //  - DiContainer: for dev and prod env
        //  - TestDiContainer: for phpunit tests
        //  - FunctionalTestDiContainer: for behat tests
        if (in_array($container->getParameter('kernel.environment'), ['test', 'ci'])) {
            if (php_sapi_name() === 'cli') {
                // phpunit tests run in CLI, so if the environment is test and
                // we're on CLI: use the test container.
                $this->_diContainer = new EngineBlock_Application_TestDiContainer($container);
            } else {
                // Non-cli requests in the test environment must be behat!
                $this->_diContainer = new EngineBlock_Application_FunctionalTestDiContainer($container);
            }
        } else {
            $this->_diContainer = new EngineBlock_Application_DiContainer($container);
        }

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
    public function reportError(Throwable $exception, $messageSuffix = '')
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
        @session_start();
        $this->getSession()->set('feedbackInfo', $this->collectFeedbackInfo($exception));

        // flush all messages in queue, something went wrong!
        $this->flushLog('An error was caught');

        return true;
    }

    /**
     * @param Exception $exception
     * @return array
     */
    public function collectFeedbackInfo(Throwable $exception)
    {
        $logRequestId = $this->getLogRequestId();
        if ($logRequestId === null) {
            $logRequestId = 'N/A - application not yet bootstrapped';
        } else {
            $logRequestId = $logRequestId->get();
        }

        $feedbackInfo = array();
        $feedbackInfo['requestId'] = $logRequestId;
        $feedbackInfo['ipAddress'] = $this->getClientIpAddress();
        $feedbackInfo['artCode'] = Art::forException($exception);

        // @todo  reset this when login is succesful
        // Find the current identity provider
        if (isset($_SESSION['currentServiceProvider'])) {
            $feedbackInfo['serviceProvider'] = $_SESSION['currentServiceProvider'];
            $spEntityId = $_SESSION['currentServiceProvider'];
            $feedbackInfo['serviceProviderName'] = $this->getServiceProviderName($spEntityId);
        }

        if (isset($_SESSION['proxyServiceProvider'])) {
            $feedbackInfo['proxyServiceProvider'] = $_SESSION['proxyServiceProvider'];
        }

        // @todo  reset this when login is succesful
        // Find the current identity provider
        if (isset($_SESSION['currentIdentityProvider'])) {
            $idpEntityId = $_SESSION['currentIdentityProvider'];
            $feedbackInfo['identityProvider'] = $idpEntityId;
            $feedbackInfo['identityProviderName'] = $this->getidentityProviderName($idpEntityId);
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
        $trustedProxyIpAddresses = $this->getDiContainer()->getTrustedProxiesIpAddresses();

        $hasForwardedFor = isset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $hasClientIp     = isset($_SERVER['HTTP_CLIENT_IP']);
        $hasRemoteAddr   = isset($_SERVER['REMOTE_ADDR']);
        $isRemoteAddrTrusted = $hasRemoteAddr && in_array($_SERVER['REMOTE_ADDR'], $trustedProxyIpAddresses);

        if ($hasForwardedFor && $hasRemoteAddr && $isRemoteAddrTrusted) {
            // Use the forwarded IP address, typically set when the
            // client is using a proxy server.
            // Format: "X-Forwarded-For: client1, proxy1, proxy2"
            $client_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return array_shift($client_ips);
        }

        if ($hasClientIp && $hasRemoteAddr && $isRemoteAddrTrusted)
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
        $this->getSession()->set('feedbackInfo', array_merge($feedbackInfo, $this->getSession()->get('feedbackInfo', [])));
        $this->getHttpResponse()->setRedirectUrl($feedbackUrl);
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
     * @return null|RequestId
     */
    public function getLogRequestId()
    {
        return $this->_requestId;
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
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession()
    {
        return $this->getDiContainer()->getSession();
    }

    /**
     * @return \EngineBlock_Application_DiContainer
     */
    public function getDiContainer()
    {
        return $this->_diContainer;
    }

    /**
     * @param string $serviceProviderId
     * @return string
     */
    private function getServiceProviderName(string $serviceProviderId){
        try {
            $serviceProvider = $this->getDiContainer()->getMetadataRepository()->fetchServiceProviderByEntityId($serviceProviderId);
            return $serviceProvider->getDisplayName($this->getDiContainer()->getLocaleProvider()->getLocale());
        } catch (EntityNotFoundException $e) {}

        return '';
    }

    private function getIdentityProviderName(string $identityProviderId): string
    {
        try {
            $identityProvider = $this->getDiContainer()->getMetadataRepository()->fetchIdentityProviderByEntityId($identityProviderId);
            return $identityProvider->getDisplayName($this->getDiContainer()->getLocaleProvider()->getLocale());
        } catch (EntityNotFoundException $e) {}

        return '';
    }
}
