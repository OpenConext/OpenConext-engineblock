<?php

/**
 * Copyright 2025 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Sbs\Dto;

use JsonSerializable;
use OpenConext\EngineBlock\Assert\Assertion;

class AuthzRequest implements JsonSerializable
{
    /**
     * @var string
     */
    public $userId;

    /**
     * @var string
     */
    public $eduPersonPrincipalName;

    /**
     * @var string
     */
    public $continueUrl;

    /**
     * @var string
     */
    public $serviceId;

    /**
     * @var string
     */
    public $issuerId;

    public static function create(
        string $userId,
        string $eppn,
        string $continueUrl,
        string $serviceId,
        string $issuerId
    ) : AuthzRequest {
        Assertion::string($userId, 'The userId must be a string.');
        Assertion::string($eppn, 'The eduPersonPrincipalName must be a string.');
        Assertion::string($continueUrl, 'The continueUrl must be a string.');
        Assertion::string($serviceId, 'The serviceId must be a string.');
        Assertion::string($issuerId, 'The issuerId must be a string.');

        $request = new self();
        $request->userId = $userId;
        $request->eduPersonPrincipalName = $eppn;
        $request->continueUrl = $continueUrl;
        $request->serviceId = $serviceId;
        $request->issuerId = $issuerId;

        return $request;
    }

    public function jsonSerialize() : array
    {
        return [
            'user_id' => $this->userId,
            'eppn' => $this->eduPersonPrincipalName,
            'continue_url' => $this->continueUrl,
            'service_id' => $this->serviceId,
            'issuer_id' => $this->issuerId
        ];
    }
}
