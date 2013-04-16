<?php
class EngineBlock_Translate_CacheProxy
    extends EngineBlock_Cache_FileCacheProxyAbstract
{
    protected function getCacheKey()
    {
        return 'translate';
    }

    /**
     * @param $cacheData
     * @return bool
     */
    protected function _isCacheValid($cachedData)
    {
        return $cachedData instanceof Zend_Translate;
    }

    /**
     * @param array $files
     */
    protected function _loadFromFiles(array $files)
    {
        $translate = new Zend_Translate(
            'Array',
            $files['en'],
            'en'
        );

        $translate->addTranslation(
            array(
                'content' => $files['nl'],
                'locale'  => 'nl'
            )
        );

        return $translate;
    }
}
