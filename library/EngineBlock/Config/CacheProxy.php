<?php
/**
 * @todo write unit tests
 */
class EngineBlock_Config_CacheProxy extends EngineBlock_Cache_FileCacheProxyAbstract
{
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
        $flatResults = array();
        foreach ($files as $iniFile) {
            $parsed = parse_ini_file($iniFile);
            $flatResults = array_merge($flatResults, $parsed);
        }

        $nestedResults = array();
        foreach ($flatResults as $key => $value) {
            $keyParts = explode('.', $key);
            $pointer = &$nestedResults;

            foreach ($keyParts as $keyPart) {
                if (!isset($pointer[$keyPart])) {
                    $pointer[$keyPart] = array();
                }
                $pointer = &$pointer[$keyPart];
            }

            $pointer = $value;
        }

        return new Zend_Config($nestedResults);
    }

    /**
     * @param $cachedData
     * @return bool
     */
    protected function _isCacheValid($cachedData)
    {
        return $cachedData instanceof Zend_Config;
    }
}
