<?php
abstract class EngineBlock_Cache_FileCacheProxyAbstract
{
    /**
     * @var array
     */
    protected $_files;

    /**
     * @param array $files
     */
    public function __construct(array $files)
    {
        $this->_files = $files;
    }

    abstract protected function getCacheKey();

    /**
     * Tries to load from cache, if this fails load it from files
     *
     * @param array $files
     * @return mixed
     */
    public function load()
    {
        $timestamp = $this->_getTimestampFromFiles($this->_files);
        $data = $this->_loadFromCache($timestamp);

        if (!$data) {
            $data = $this->_loadFromFiles($this->_files);
            $this->_storeInCache($timestamp, $data);
        }

        return $data;
    }

    /**
     * Returns the timestamp of the newest file
     *
     * @param array $files
     * @return int|null
     */
    protected function _getTimestampFromFiles(array $files)
    {
        $timestamp = null;
        foreach ($files as $confileFile) {
            $fileTimestamp = filemtime($confileFile);
            if ($fileTimestamp > $timestamp) {
                $timestamp = $fileTimestamp;
            }
        }

        return $timestamp;
    }

    abstract protected function _loadFromFiles(array $files);

    abstract protected function _isCacheValid($cachedData);

    /**
     * @param array $files
     * @return mixed
     */
    protected function _loadFromCache($timestamp)
    {
        // Try to get from cache
        if ($this->isApcEnabled()) {
            $cache = apc_fetch($this->getCacheKey());

            $isCacheValid = $this->_isCacheValid($cache['data']) &&
                $cache['timestamp'] === $timestamp;
            if ($isCacheValid) {
                return $cache['data'];
            }
        }
    }

    /**
     * @param $timestamp
     * @param mixed $data
     */
    protected function _storeInCache($timestamp, $data)
    {
        if ($this->isApcEnabled()) {
            $cache['data'] = $data;
            $cache['timestamp'] = $timestamp;
            apc_add($this->getCacheKey(), $cache);
        }
    }

    /**
     * @return bool
     */
    private function isApcEnabled() {
        return extension_loaded('apc') && ini_get('apc.enabled');
    }
}
