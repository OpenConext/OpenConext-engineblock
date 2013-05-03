<?php
/**
 * @todo write a test
 */
class EngineBlock_Corto_Model_Consent_Factory
{
    /**
     * Creates a new Consent instance
     *
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @param string $userId
     * @param string $serviceProviderEntityId
     * @param array $attributes
     * @return EngineBlock_Corto_Model_Consent
     */
    public function create(EngineBlock_Corto_ProxyServer $proxyServer, $userId, $serviceProviderEntityId, array $attributes) {
        return new EngineBlock_Corto_Model_Consent(
            $this->hashUserId($userId),
            $serviceProviderEntityId,
            $this->hashAttributes($attributes, $proxyServer->getConfig('ConsentStoreValues', true))
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

    /**
     * @param string $userId
     * @return string
     */
    private function hashUserId($userId)
    {
        return sha1($userId);
    }

    /**
     * @param array $response
     * @return string
     */
    public static function extractUidFromResponse(array $response) {
        if (!isset($response['saml:Assertion']['saml:Subject']['saml:NameID']['__v']))
        {
            throw new EngineBlock_Exception('Name id is not set');
        }

        return $response['saml:Assertion']['saml:Subject']['saml:NameID']['__v'];
    }
}