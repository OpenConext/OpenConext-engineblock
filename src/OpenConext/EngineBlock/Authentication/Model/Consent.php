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
