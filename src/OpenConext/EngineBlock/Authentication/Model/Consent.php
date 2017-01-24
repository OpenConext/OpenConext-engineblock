<?php

namespace OpenConext\EngineBlock\Authentication\Model;

use DateTime;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;

final class Consent
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
     * @param string $userId
     * @param string $serviceProviderEntityId
     * @param DateTime $consentGivenOn
     * @param ConsentType $consentType
     */
    public function __construct(
        $userId,
        $serviceProviderEntityId,
        DateTime $consentGivenOn,
        ConsentType $consentType
    ) {
        Assertion::nonEmptyString($userId, 'userId');
        Assertion::nonEmptyString($serviceProviderEntityId, 'serviceProviderEntityId');

        $this->userId                  = $userId;
        $this->serviceProviderEntityId = $serviceProviderEntityId;
        $this->consentGivenOn          = $consentGivenOn;
        $this->consentType             = $consentType;
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
}
