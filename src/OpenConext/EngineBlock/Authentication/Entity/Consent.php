<?php

namespace OpenConext\EngineBlock\Authentication\Entity;

use DateTime;
use OpenConext\EngineBlock\Authentication\Exception\InvalidArgumentException;

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
    private $serviceId;

    /**
     * @var DateTime
     */
    private $consentDate;

    /**
     * @var DateTime
     */
    private $usageDate;

    /**
     * @param string   $userId
     * @param string   $serviceId
     * @param DateTime $consentDate
     * @param DateTime $usageDate
     */
    public function __construct($userId, $serviceId, DateTime $consentDate, DateTime $usageDate)
    {
        if (!is_string($userId)) {
            throw InvalidArgumentException::invalidType('string', 'userId', $userId);
        }

        if (!is_string($serviceId)) {
            throw InvalidArgumentException::invalidType('string', 'serviceId', $serviceId);
        }

        $this->userId      = $userId;
        $this->serviceId   = $serviceId;
        $this->consentDate = $consentDate;
        $this->usageDate   = $usageDate;
    }
}
