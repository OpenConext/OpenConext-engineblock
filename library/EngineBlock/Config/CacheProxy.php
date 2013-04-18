<?php
/**
 * @todo write unit tests
 */
class EngineBlock_Config_CacheProxy
    extends  EngineBlock_Cache_FileCacheProxyAbstract {

    protected function getCacheKey()
    {
        return 'config';
    }

    /**
     * Tries to parse config files, if this fails each file will be verified to provide more debug information
     *
     * @param array $files
     * @return EngineBlock_Config_Ini
     */
    protected function _loadFromFiles(array $files)
    {
        try {
            return new EngineBlock_Config_Ini($this->_mergeFiles($files));
        } catch (EngineBlock_Exception $ex) {
            $this->_verifyFiles($files);
        }
    }

    /**
     * Merges content of given config files
     *
     * @param array $files
     * @return string
     */
    private function _mergeFiles(array $files)
    {
        $configFileContents = "";
        foreach ($files as $configFile) {
            $configFileContents .= file_get_contents($configFile) . PHP_EOL;
        }
        return $configFileContents;
    }

    /**
     * Tries to parse config files, if this fails an exception will be thrown in EngineBlock_Config_Ini, this is useful
     * to determine which of the files contains an error
     *
     * @param array $files
     */
    private function _verifyFiles(array $files)
    {
        /** @var $config EngineBlock_Config_Ini */
        foreach ($files as $configFile) {
            new EngineBlock_Config_Ini($configFile);
        }
    }

    /**
     * @param $cacheData
     * @return bool
     */
    protected function _isCacheValid($cachedData)
    {
        return $cachedData instanceof Zend_Config;
    }
}