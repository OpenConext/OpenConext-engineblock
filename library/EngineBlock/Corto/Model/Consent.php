<?php
class EngineBlock_Corto_Model_Consent
{
    /** @var string */
    protected $userIdHash;

    /** @var string */
    protected $serviceProviderEntityId;

    /** @var string */
    protected $attributesHash;

    /**
     * @var DateTime
     */
    protected $usageDate;

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
        $this->userIdHash = $userIdHash;
        $this->serviceProviderEntityId = $serviceProviderEntityId;
        $this->attributesHash = $attributesHash;
        $this->usageDate = $usageDate;
    }

    /**
     * @return string
     */
    public function getAttributesHash()
    {
        return $this->attributesHash;
    }

    /**
     * @return string
     */
    public function getServiceProviderEntityId()
    {
        return $this->serviceProviderEntityId;
    }

    /**
     * @return string
     */
    public function getUserIdHash()
    {
        return $this->userIdHash;
    }

    /**
     * @param \DateTime $usageDate
     */
    public function setUsageDate($usageDate)
    {
        $this->usageDate = $usageDate;
        return $this;
    }
}
