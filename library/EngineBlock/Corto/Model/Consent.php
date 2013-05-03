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
     * @var DateTime
     */
    private $_usageDate;

    /**
     * @param string $userIdHash
     * @param string $serviceProviderEntityId
     * @param string $attributesHash
     * @param DateTime $usageDate
     */
    public function __construct(
        $userIdHash,
        $serviceProviderEntityId,
        $attributesHash,
        DateTime $usageDate
    ){
        $this->_userIdHash = $userIdHash;
        $this->_serviceProviderEntityId = $serviceProviderEntityId;
        $this->_attributesHash = $attributesHash;
        $this->_usageDate = $usageDate;
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

    /**
     * @param \DateTime $usageDate
     */
    public function setUsageDate($usageDate)
    {
        $this->_usageDate = $usageDate;
        return $this;
    }
}
