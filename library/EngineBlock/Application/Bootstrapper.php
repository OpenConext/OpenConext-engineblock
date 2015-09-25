<?php

class EngineBlock_Application_Bootstrapper
{
    const CONFIG_FILE_DEFAULT       = 'configs/application.ini';
    const CONFIG_FILE_ENVIRONMENT   = '/etc/surfconext/engineblock.ini';
    const P3P_HEADER = 'CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"';

    /**
     * @var EngineBlock_ApplicationSingleton
     */
    protected $_application;

    /**
     * @var bool
     */
    protected $_bootstrapped = false;

    /**
     * @param EngineBlock_ApplicationSingleton $application
     */
    public function __construct(EngineBlock_ApplicationSingleton $application)
    {
        $this->_application = $application;
    }

    /**
     * Bootstrap the application.
     *
     * Note that the order or bootstrapping is very important.
     *
     * @return EngineBlock_ApplicationSingleton Bootstrapped application singleton
     */
    public function bootstrap()
    {
        if ($this->_bootstrapped) {
            return $this;
        }

        $this->_bootstrapDefaultDiContainer();
        $this->_bootstrapConfiguration();
        $this->_bootstrapTestDiContainer();

        $this->_bootstrapPhpSettings();
        $this->_bootstrapErrorReporting();
        $this->_bootstrapLogging();

        $this->_bootstrapSuperGlobalOverrides();
        $this->_bootstrapHttpCommunication();
        $this->_bootstrapSaml2();

        $this->_bootstrapLayout();
        $this->_bootstrapTranslations();

        $this->_bootstrapped = true;

        return $this;
    }

    protected function _bootstrapDefaultDiContainer()
    {
        $this->_application->setDiContainer(new EngineBlock_Application_DiContainer());
    }

    protected function _bootstrapConfiguration()
    {
        if ($this->_application->getConfiguration()) {
            return;
        }

        $configProxy = new EngineBlock_Config_CacheProxy(
            $this->_getAllConfigFiles(),
            $this->_application->getDiContainer()->getApplicationCache()
        );
        $this->_application->setConfiguration($configProxy->load());
    }

    protected function _bootstrapTestDiContainer()
    {
        if ($this->_application->getConfigurationValue('testing', false)) {
            $this->_application->setDiContainer(new EngineBlock_Application_TestDiContainer());
            return;
        }

        if ($this->_application->getConfigurationValue('functionalTesting', false)) {
            $this->_application->setDiContainer(new EngineBlock_Application_FunctionalTestDiContainer());
            return;
        }
    }

    /**
     * We need this because we may need to trick EngineBlock into thinking it's hosting on a different URL.
     *
     * Known uses:
     * * SAML Replaying with OpenConext-Engine-Test-Stand.
     *
     */
    protected function _bootstrapSuperGlobalOverrides()
    {
        $superGlobalManager = $this->_application->getDiContainer()->getSuperGlobalManager();
        if (!$superGlobalManager) {
            return;
        }

        $superGlobalManager->injectOverrides();
    }

    /**
     * Return a list of config files (default and environment overrides) that should be loaded
     *
     * @return array
     */
    protected function _getAllConfigFiles()
    {
        return array(
            ENGINEBLOCK_FOLDER_APPLICATION . self::CONFIG_FILE_DEFAULT,
            self::CONFIG_FILE_ENVIRONMENT,
        );
    }

    protected function _bootstrapLogging()
    {
        $configuration = $this->_application->getConfiguration();

        if (!isset($configuration->logger)) {
            throw new EngineBlock_Exception(
                "No logger configuration defined! Logging is required, please configure the logger under the logger " .
                "key in your application.ini. See EngineBlock_Log_MonologLoggerFactory's docblock for more details.",
                EngineBlock_Exception::CODE_ALERT
            );
        }

        $loggerConfiguration = $configuration->logger->toArray();

        /** @var string|EngineBlock_Log_LoggerFactory $loggerFactory */
        $loggerFactory = $loggerConfiguration['factory'];
        EngineBlock_Log_InvalidConfigurationException::assertIsValidFactory(
            $loggerFactory,
            'EngineBlock_Log_LoggerFactory'
        );
        $logger = $loggerFactory::factory($loggerConfiguration['conf'], $configuration->debug);

        $this->_application->setLogInstance($logger);
        $this->_application->setLogRequestId(uniqid());
    }

    protected function _bootstrapHttpCommunication()
    {
        $httpRequest = EngineBlock_Http_Request::createFromEnvironment();
        $this->_application->getLogInstance()->info(
            sprintf(
                'Handling incoming request: %s %s',
                $httpRequest->getMethod(),
                $httpRequest->getUri()
            )
        );
        $this->_application->setHttpRequest($httpRequest);

        $response = new EngineBlock_Http_Response();
        $response->setHeader('Strict-Transport-Security', 'max-age=15768000; includeSubDomains');
        // workaround, P3P is needed to support iframes like iframe gadgets in portals
        $response->setHeader('P3P', self::P3P_HEADER);
        $this->_application->setHttpResponse($response);
    }

    private function _bootstrapSaml2()
    {
        $container = new EngineBlock_Saml2_Container($this->_application->getLogInstance());
        SAML2_Compat_ContainerSingleton::setContainer($container);
    }

    protected function _bootstrapPhpSettings()
    {
        $settings = $this->_application->getConfiguration()->phpSettings->toArray();
        $this->_setIniSettings($settings);
        // prevent any XXE attacks when processing XML
//        libxml_disable_entity_loader();
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
        $errorHandler = new EngineBlock_Application_ErrorHandler($this->_application);
        register_shutdown_function  (array($errorHandler, 'shutdown'));
        set_error_handler           (array($errorHandler, 'error'));
        set_exception_handler       (array($errorHandler, 'exception'));

        $this->_application->setErrorHandler($errorHandler);
    }

    protected function _bootstrapLayout()
    {
        $layout = new Zend_Layout();

        // Set a layout script path:
        $layout->setLayoutPath(ENGINEBLOCK_FOLDER_APPLICATION . 'layouts/scripts/');

        // Defaults
        $defaultsConfig = $this->_application->getConfiguration()->defaults;
        $layout->title  = $defaultsConfig->title;
        $layout->header = $defaultsConfig->header;

        // choose a different layout script:
        $layout->setLayout($defaultsConfig->layout);

        $this->_application->setLayout($layout);
    }

    protected function _bootstrapTranslations()
    {
        $translationFiles = array(
            'en' => ENGINEBLOCK_FOLDER_ROOT . 'languages/en.php',
            'nl' => ENGINEBLOCK_FOLDER_ROOT . 'languages/nl.php'
        );
        $translationCacheProxy = new EngineBlock_Translate_CacheProxy(
            $translationFiles,
            $this->_application->getDiContainer()->getApplicationCache()
        );

        $translate = $translationCacheProxy->load();

        // If the URL has &lang=nl in it or the lang var is posted, or a lang cookie was set, then use that locale
        $httpRequest = $this->_application->getHttpRequest();
        $cookieLang = $httpRequest->getCookie('lang');
        $getLang    = $httpRequest->getQueryParameter('lang');
        $postLang   = $httpRequest->getPostParameter('lang');

        $lang = null;
        if ($getLang) {
            $lang = strtolower($getLang);
        } else if ($postLang) {
            $lang = strtolower($postLang);
        } else {
            $lang = strtolower($cookieLang);
        }

        $langCookieConfig = $this->_application->getConfigurationValue('cookie')->lang;
        $cookieDomain = $langCookieConfig->domain;
        $cookieExpiry = null;
        if (isset($langCookieConfig->expiry) && $langCookieConfig->expiry > 0) {
            $cookieExpiry =  time() + $langCookieConfig->expiry;
        }

        if ($lang && $translate->getAdapter()->isAvailable($lang)) {
            $translate->setLocale($lang);
            $this->_application->getHttpResponse()->setCookie('lang', $lang, $cookieExpiry, '/', $cookieDomain);
        }
        else {
            $translate->setLocale('en');
            $this->_application->getHttpResponse()->setCookie('lang', 'en', $cookieExpiry, '/', $cookieDomain);
        }

        $this->_application->setTranslator($translate);
    }
}
