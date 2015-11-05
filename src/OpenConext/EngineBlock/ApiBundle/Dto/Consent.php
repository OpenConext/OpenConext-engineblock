<?php

namespace OpenConext\EngineBlock\ApiBundle\Dto;

use DateTime;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Authentication\Entity\Consent as ConsentEntity;

final class Consent
{
    /**
     * The service for which attribute release consent was given.
     *
     * @var ServiceLocaleMap
     */
    public $service;

    /**
     * @var DateTime
     */
    public $consentGivenOn;

    /**
     * @var DateTime
     */
    public $lastUsedOn;

    public static function fromConsentAndServiceProvider(ConsentEntity $consent, ServiceProvider $serviceProvider)
    {
        $dto = new Consent();
        $dto->service = ServiceLocaleMap::fromServiceProvider($serviceProvider);
        $dto->consentGivenOn = $consent->getDateConsentWasGivenOn();
        $dto->lastUsedOn = $consent->getDateLastUsedOn();

        return $dto;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'service'          => $this->service->jsonSerialize(),
            'consent_given_on' => $this->consentGivenOn->format(DateTime::ATOM),
            'last_used_on'     => $this->lastUsedOn->format(DateTime::ATOM),
        );
    }
}
