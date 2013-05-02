<?php
/**
 * @todo write a test
 */
class EngineBlock_Corto_Model_Consent_Factory
{
    /** @var EngineBlock_Database_ConnectionFactory */
    private $_databaseConnectionFactory;

     /**
      * @param EngineBlock_Database_ConnectionFactory $databaseConnectionFactory
      */
    public function __construct(
        EngineBlock_Database_ConnectionFactory $databaseConnectionFactory
    )
    {
        $this->_databaseConnectionFactory = $databaseConnectionFactory;
    }

    /**
     * Creates a new Consent instance
     *
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @param array $response
     * @param array $attributes
     * @return EngineBlock_Corto_Model_Consent
     */
    public function create(EngineBlock_Corto_ProxyServer $proxyServer, array $response, array $attributes) {
        return new EngineBlock_Corto_Model_Consent(
            $proxyServer->getConfig('ConsentDbTable', 'consent'),
            $response,
            $this->hashAttributes($attributes, $proxyServer->getConfig('ConsentStoreValues', true)),
            $this->_databaseConnectionFactory
        );
    }

    /**
     * @param array $attributes
     * @param bool $mustStoreValues
     * @return string
     */
    private function hashAttributes(array $attributes, $mustStoreValues)
    {
        $hashBase = NULL;
        if ($mustStoreValues) {
            ksort($attributes);
            $hashBase = serialize($attributes);
        } else {
            $names = array_keys($attributes);
            sort($names);
            $hashBase = implode('|', $names);
        }
        return sha1($hashBase);
    }
}