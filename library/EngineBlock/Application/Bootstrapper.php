<?php

class EngineBlock_Application_Bootstrapper
{
    const CONFIG_FILE_DEFAULT       = 'configs/application.ini';
    // @todo correct typo
    const CONFIG_FILE_ENVIORNMENT   = '/etc/surfconext/engineblock.ini';
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

        $this->_setEnvironmentIdByEnvironment();

        $this->_bootstrapDiContainer();

        $this->_bootstrapConfiguration();

        $this->_setEnvironmentIdByDetection();

        $this->_bootstrapEnvironmentConfiguration();

        $this->_bootstrapPhpSettings();
        $this->_bootstrapErrorReporting();
        $this->_bootstrapLogging();
        $this->_bootstrapHttpCommunication();

        $this->_bootstrapLayout();
        $this->_bootstrapTranslations();

        $this->_bootstrapped = true;

        return $this;
    }

    protected function _bootstrapDiContainer() {
        if (ENGINEBLOCK_ENV == 'testing') {
            $this->_application->setDiContainer(new EngineBlock_Application_TestDiContainer());
        } elseif (ENGINEBLOCK_ENV == 'functional-testing') {
            $this->_application->setDiContainer(new EngineBlock_Application_FunctionalTestDiContainer());
        } else {
            $this->_application->setDiContainer(new EngineBlock_Application_DiContainer());
        }
    }

    protected function _bootstrapConfiguration()
    {
        $configProxy = new EngineBlock_Config_CacheProxy(
            $this->_getAllConfigFiles(),
            $this->_application->getDiContainer()->getApplicationCache()
        );
        $this->_application->setConfiguration($configProxy->load());
    }

    /**
     * return a list of config files (default and environment overrides) that shoud be loaded
     *
     * @return array
     */
    protected function _getAllConfigFiles()
    {
        return array(
            ENGINEBLOCK_FOLDER_APPLICATION . self::CONFIG_FILE_DEFAULT,
            self::CONFIG_FILE_ENVIORNMENT,
        );
    }

    protected function _setEnvironmentIdByDetection()
    {
        if ($this->_application->getEnvironmentId()) {
            // Detection not required.
            return;
        }

        foreach ($this->_application->getConfiguration() as $environmentId => $environmentConfiguration) {
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
                $this->_application->setEnvironmentId($environmentId);
                define('ENGINEBLOCK_ENV', $environmentId);
                return;
            }
        }

        throw new EngineBlock_Application_Bootstrapper_Exception(
            'Unable to detect an environment!'
        );
    }

    protected function _bootstrapEnvironmentConfiguration()
    {
        $env = $this->_application->getEnvironmentId();
        if (!isset($this->_application->getConfiguration()->$env)) {
            throw new EngineBlock_Application_Bootstrapper_Exception("Environment '$env' does not exist?!?");
        }

        $this->_application->setConfiguration(
            $this->_application->getConfiguration()->$env
        );
    }

    protected function _bootstrapLogging()
    {
        if (!isset($this->_application->getConfiguration()->logs)) {
            throw new EngineBlock_Exception(
                "No logs defined! Logging is required, please set logs. in your application.ini",
                EngineBlock_Exception::CODE_ALERT
            );
        }

        $this->_application->setLogInstance(
            EngineBlock_Log::factory($this->_application->getConfiguration()->logs)
        );
    }

    protected function _bootstrapHttpCommunication()
    {
        $httpRequest = EngineBlock_Http_Request::createFromEnvironment();
        $this->_application->getLogInstance()->log(
            sprintf(
                'Handling incoming request: %s %s',
                $httpRequest->getMethod(),
                $httpRequest->getUri()
            ),
            Zend_Log::INFO
        );
        $this->_application->setHttpRequest($httpRequest);

        $response = new EngineBlock_Http_Response();
        $response->setHeader('Strict-Transport-Security', 'max-age=15768000; includeSubDomains');
        // workaround, P3P is needed to support iframes like iframe gadgets in portals
        $response->setHeader('P3P', self::P3P_HEADER);
        $this->_application->setHttpResponse($response);
    }

    protected function _bootstrapPhpSettings()
    {
        $settings = $this->_application->getConfiguration()->phpSettings->toArray();
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

    protected function _setEnvironmentIdByEnvironment()
    {
        // Get from environment variable (from Apache or the shell)
        if (!defined('ENGINEBLOCK_ENV') && getenv('ENGINEBLOCK_ENV')) {
            define('ENGINEBLOCK_ENV', getenv('ENGINEBLOCK_ENV'));
        }
        // Get from predefined constant
        if (defined('ENGINEBLOCK_ENV')) {
            $this->_application->setEnvironmentId(ENGINEBLOCK_ENV);
        }
    }
}