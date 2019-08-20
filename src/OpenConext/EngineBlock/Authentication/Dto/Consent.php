<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Authentication\Dto;

use DateTime;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
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
            'support_url' => [
                'en' => $this->serviceProvider->supportUrlEn,
                'nl' => $this->serviceProvider->supportUrlNl,
            ],
            'eula_url' => $this->serviceProvider->getCoins()->termsOfServiceUrl(),
            'support_email' => $supportEmail,
            'name_id_format' => $this->serviceProvider->nameIdFormat,
        ];

        $serviceProvider += $this->getDisplayNameFields();

        return [
            'service_provider' => $serviceProvider,
            'consent_given_on' => $this->consent->getDateConsentWasGivenOn()->format(DateTime::ATOM),
            'consent_type'     => $this->consent->getConsentType()->jsonSerialize(),
        ];
    }

    private function getDisplayNameFields()
    {
        if (!empty($this->serviceProvider->displayNameEn)) {
            $fields['display_name']['en'] = $this->serviceProvider->displayNameEn;
        } elseif (!empty($this->serviceProvider->nameEn)) {
            $fields['display_name']['en'] = $this->serviceProvider->nameEn;
        } else {
            $fields['display_name']['en'] = $this->serviceProvider->entityId;
        }

        if (!empty($this->serviceProvider->displayNameNl)) {
            $fields['display_name']['nl'] = $this->serviceProvider->displayNameNl;
        } elseif (!empty($this->serviceProvider->nameNl)) {
            $fields['display_name']['nl'] = $this->serviceProvider->nameNl;
        } else {
            $fields['display_name']['nl'] = $this->serviceProvider->entityId;
        }

        return $fields;
    }
}
