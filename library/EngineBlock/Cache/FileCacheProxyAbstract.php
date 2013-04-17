<?php
abstract class EngineBlock_Cache_FileCacheProxyAbstract
{
    /**
     * @var Zend_Cache_Backend_Apc
     */
    private $applicationCache;

    /**
     * @var array
     */
    protected $_files;

    /**
     * @param array $files
     * @param null|Zend_Cache_Backend_Apc $applicationCache
     */
    public function __construct(array $files, Zend_Cache_Backend_Apc $applicationCache = null)
    {
        $this->_files = $files;
        $this->applicationCache = $applicationCache;
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
            $this->_storeInCache($data);
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
     * Tries to load data from cache if available
     *
     * @param int $timestamp
     * @return mixed
     */
    protected function _loadFromCache($timestamp)
    {
        if (!$this->applicationCache instanceof Zend_Cache_Backend_Apc) {
            return;
        }

        if ($timestamp > $this->applicationCache->test($this->getCacheKey())) {
            return;
        }

        $cache = $this->applicationCache->load($this->getCacheKey());

        if (!$this->_isCacheValid($cache)) {
            return;
        }

        return $cache;
    }

    /**
     * @param mixed $data
     */
    protected function _storeInCache($data)
    {
        if (!$this->applicationCache instanceof Zend_Cache_Backend_Apc) {
            return;
        }

        $this->applicationCache->save($data, $this->getCacheKey());
    }
}
