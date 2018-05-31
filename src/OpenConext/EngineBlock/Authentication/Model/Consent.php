<?php

namespace OpenConext\EngineBlock\Authentication\Model;

use DateTime;
use JsonSerializable;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;

final class Consent implements JsonSerializable
{
    /**
     * The NameID of the user.
     *
     * @var string
     */
    private $userId;

    /**
     * The entity ID of the service.
     *
     * @var string
     */
    private $serviceProviderEntityId;

    /**
     * @var DateTime
     */
    private $consentGivenOn;

    /**
     * @var ConsentType
     */
    private $consentType;

    /**
     * @var string
     */
    private $attributeHash;

    /**
     * @param string $userId
     * @param string $serviceProviderEntityId
     * @param DateTime $consentGivenOn
     * @param ConsentType $consentType
     * @param string $attributeHash
     */
    public function __construct(
        $userId,
        $serviceProviderEntityId,
        DateTime $consentGivenOn,
        ConsentType $consentType,
        $attributeHash = null
    ) {
        Assertion::nonEmptyString($userId, 'userId');
        Assertion::nonEmptyString($serviceProviderEntityId, 'serviceProviderEntityId');

        $this->userId                  = $userId;
        $this->serviceProviderEntityId = $serviceProviderEntityId;
        $this->consentGivenOn          = $consentGivenOn;
        $this->consentType             = $consentType;
        $this->attributeHash           = $attributeHash;
    }

    /**
     * The entity ID of the service.
     *
     * @return string
     */
    public function getServiceProviderEntityId()
    {
        return $this->serviceProviderEntityId;
    }

    /**
     * @return DateTime
     */
    public function getDateConsentWasGivenOn()
    {
        return clone $this->consentGivenOn;
    }

    /**
     * @return ConsentType
     */
    public function getConsentType()
    {
        return $this->consentType;
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'user_id' => $this->userId,
            'service_provider_entity_id' => $this->getServiceProviderEntityId(),
            'consent_given_on' => $this->getDateConsentWasGivenOn()->format(DateTime::ATOM),
            'consent_type' => $this->getConsentType(),
            'attribute_hash' => $this->attributeHash,
        ];
    }
}
