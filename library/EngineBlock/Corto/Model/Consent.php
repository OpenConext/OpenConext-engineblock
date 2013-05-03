<?php
class EngineBlock_Corto_Model_Consent
{
    /** @var string */
    private $_userIdHash;

    /** @var string */
    private $_serviceProviderEntityId;

    /** @var string */
    private $_attributesHash;

    /**
     * @param $userIdHash
     * @param $serviceProviderEntityId
     * @param $attributesHash
     */
    public function __construct(
        $userIdHash,
        $serviceProviderEntityId,
        $attributesHash
    ){
        $this->_userIdHash = $userIdHash;
        $this->_serviceProviderEntityId = $serviceProviderEntityId;
        $this->_attributesHash = $attributesHash;
    }

    /**
     * @return string
     */
    public function getAttributesHash()
    {
        return $this->_attributesHash;
    }

    /**
     * @return string
     */
    public function getServiceProviderEntityId()
    {
        return $this->_serviceProviderEntityId;
    }

    /**
     * @return string
     */
    public function getUserIdHash()
    {
        return $this->_userIdHash;
    }
}
