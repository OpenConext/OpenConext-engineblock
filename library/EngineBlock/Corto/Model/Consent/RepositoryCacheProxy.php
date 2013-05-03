<?php
class EngineBlock_Corto_Model_Consent_RepositoryCacheProxy
    extends EngineBlock_Corto_Model_Consent_Repository
{
    const CACHE_EXPIRATION_TIME = 2592000; // 30 days in seconds
    const CACHE_NAMESPACE = 'consent';
    const QUEUE_NAME = 'consent';

    /** @var Redis */
    private $redisClient;

    /**
     * @param Redis $redisClient
     * @return void
     * @internal param \EngineBlock_Corto_Filter_Command_Factory $filterCommandFactory
     */
    public function setRedisClient(Redis $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    public function isStored(EngineBlock_Corto_Model_Consent $consent)
    {
        $isStored = $this->redisClient->get($this->createCacheKey($consent));
        if (!$isStored) {
            $isStored = parent::isStored($consent);
        }

        return $isStored;
    }

    /**
     * Stores new or updated consent directly in cache and queues it for storing it in db
     *
     * @param EngineBlock_Corto_Model_Consent $consent
     * @return void
     */
    public function store(EngineBlock_Corto_Model_Consent $consent)
    {
        $cacheKey = $this->createCacheKey($consent);
        $this->redisClient->set($cacheKey, true, self::CACHE_EXPIRATION_TIME);

        Resque::enqueue(self::QUEUE_NAME, 'EngineBlock_Job_StoreConsent', $consent);
    }

    /**
     * Creates unique key for each consent
     *
     * @param EngineBlock_Corto_Model_Consent $consent
     * @return string
     */
    private function createCacheKey(EngineBlock_Corto_Model_Consent $consent)
    {
        return self::CACHE_NAMESPACE . ":{$consent->getUserIdHash()}:{$consent->getServiceProviderEntityId()}:{$consent->getAttributesHash()}";
    }
}