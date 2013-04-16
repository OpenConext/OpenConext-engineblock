<?php
/**
 * @todo write unit tests
 */
class EngineBlock_Config_CacheProxy {
    /**
     * @var array
     */
    private $_configFiles;

    /**
     * @param array $configFiles
     */
    public function __construct(array $configFiles)
    {
        $this->_configFiles = $configFiles;
    }

    /**
     * Tries to load config from cache, if this fails load it from files
     *
     * @param array $configFiles
     * @return EngineBlock_Config_Ini
     */
    public function load()
    {
        $configTimestamp = $this->_getConfigTimestampFromFiles($this->_configFiles);
        $config = $this->_loadConfigFromCache($configTimestamp);

        if (!$config) {
            $config = $this->_loadConfigFromFiles($this->_configFiles);
            $this->_storeConfigInCache($configTimestamp, $config);
        }

        return $config;
    }

    /**
     * Returns the timestamp of the newest file
     *
     * @param array $configFiles
     * @return int|null
     */
    private function _getConfigTimestampFromFiles(array $configFiles)
    {
        $configTimestamp = null;
        foreach ($configFiles as $confileFile) {
            $fileTimestamp = filemtime($confileFile);
            if ($fileTimestamp > $configTimestamp) {
                $configTimestamp = $fileTimestamp;
            }
        }

        return $configTimestamp;
    }

    /**
     * Tries to parse config files, if this fails each file will be verified to provide more debug information
     *
     * @param array $configFiles
     * @return EngineBlock_Config_Ini
     */
    private function _loadConfigFromFiles(array $configFiles)
    {
        try {
            return new EngineBlock_Config_Ini($this->_mergeConfigFiles($configFiles));
        } catch (EngineBlock_Exception $ex) {
            $this->_verifyConfigFiles($configFiles);
        }
    }

    /**
     * Merges content of given config files
     *
     * @param array $configFiles
     * @return string
     */
    protected function _mergeConfigFiles(array $configFiles)
    {
        $configFileContents = "";
        foreach ($configFiles as $configFile) {
            $configFileContents .= file_get_contents($configFile) . PHP_EOL;
        }
        return $configFileContents;
    }

    /**
     * Tries to parse config files, if this fails an exception will be thrown in EngineBlock_Config_Ini, this is useful
     * to determine which of the files contains an error
     *
     * @param array $configFiles
     */
    private function _verifyConfigFiles(array $configFiles)
    {
        /** @var $config EngineBlock_Config_Ini */
        foreach ($configFiles as $configFile) {
            new EngineBlock_Config_Ini($configFile);
        }
    }

    /**
     * @param array $configFiles
     * @return mixed
     */
    private function _loadConfigFromCache($configTimestamp)
    {
        // Try to get from cache
        if ($this->isApcEnabled()) {
            $configCache = apc_fetch('config');

            $isCacheValid = $configCache['config'] instanceof Zend_Config &&
                $configCache['timestamp'] === $configTimestamp;
            if ($isCacheValid) {
                return $configCache['config'];
            }
        }
    }

    /**
     * @param $configTimestamp
     * @param Zend_Config $config
     */
    private function _storeConfigInCache($configTimestamp, Zend_Config $config)
    {
        if ($this->isApcEnabled()) {
            $configCache['config'] = $config;
            $configCache['timestamp'] = $configTimestamp;
            apc_add('config', $configCache);
        }
    }

    /**
     * @return bool
     */
    private function isApcEnabled() {
        return extension_loaded('apc') && ini_get('apc.enabled');
    }
}