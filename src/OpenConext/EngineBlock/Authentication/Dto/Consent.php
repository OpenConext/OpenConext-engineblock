<?php

namespace OpenConext\EngineBlock\Authentication\Dto;

use DateTime;
use OpenConext\Component\EngineBlockMetadata\ContactPerson;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Authentication\Model\Consent as ConsentEntity;

final class Consent
{
    const CONTACT_TYPE_SUPPORT = 'support';

    /**
     * @var ConsentEntity
     */
    private $consent;

    /**
     * @var ServiceProvider
     */
    private $serviceProvider;

    /**
     * @param ConsentEntity   $consent
     * @param ServiceProvider $serviceProvider
     */
    public function __construct(ConsentEntity $consent, ServiceProvider $serviceProvider)
    {
        $this->consent         = $consent;
        $this->serviceProvider = $serviceProvider;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $supportContacts = array_values(
            array_filter(
                $this->serviceProvider->contactPersons,
                function (ContactPerson $contact) {
                    return $contact->contactType === Consent::CONTACT_TYPE_SUPPORT;
                }
            )
        );

        $supportEmail = null;
        if (count($supportContacts) > 0) {
            $supportEmail = $supportContacts[0]->emailAddress;
        }

        $serviceProvider = [
            'entity_id'    => $this->serviceProvider->entityId,
            'display_name' => [
                'en' => $this->serviceProvider->displayNameEn,
                'nl' => $this->serviceProvider->displayNameNl,
            ],
            'support_url' => [
                'en' => $this->serviceProvider->supportUrlEn,
                'nl' => $this->serviceProvider->supportUrlNl,
            ],
            'eula_url' => $this->serviceProvider->termsOfServiceUrl,
            'support_email' => $supportEmail,
            'support_url_en' => $this->serviceProvider->supportUrlEn,
            'support_url_nl' => $this->serviceProvider->supportUrlNl,
            'name_id_format' => $this->serviceProvider->nameIdFormat,
        ];

        return [
            'service_provider' => $serviceProvider,
            'consent_given_on' => $this->consent->getDateConsentWasGivenOn()->format(DateTime::ATOM),
            'consent_type'     => $this->consent->getConsentType()->jsonSerialize(),
        ];
    }
}
