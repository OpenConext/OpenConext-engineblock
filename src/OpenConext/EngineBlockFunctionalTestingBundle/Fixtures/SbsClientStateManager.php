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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use InvalidArgumentException;
use OpenConext\EngineBlockBundle\Sbs\SbsClientInterface;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;

class SbsClientStateManager
{

    /**
     * @var AbstractDataStore
     */
    private $dataStore;

    /**
     * @var array
     */
    private $authz = [];
    /**
     * @var array
     */
    private $attributes = [];

    public function __construct(
        AbstractDataStore $dataStore
    ) {
        $this->dataStore = $dataStore;
    }

    public function prepareAuthzResponse(string $msg, ?array $attributes = null): void
    {
        if ($msg === SbsClientInterface::INTERRUPT) {
            $this->authz = [
                'msg' => SbsClientInterface::INTERRUPT,
                'nonce' => 'my-nonce',
            ];
        } elseif ($msg === SbsClientInterface::AUTHORIZED) {
            $this->authz = [
                'msg' => SbsClientInterface::AUTHORIZED,
            ];
            $this->authz += $attributes ?? $this->getValidMockAttributes();
        } elseif ($msg === SbsClientInterface::ERROR) {
            $this->authz = [
                'msg' => SbsClientInterface::ERROR,
            ];
        } else {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid authz message.', $msg));
        }

        $this->save();
    }

    public function getPreparedAuthzResponse(): array
    {
        return $this->dataStore->load()['authz'];
    }

    /**
     * @return array[]
     */
    public function getValidMockAttributes(): array
    {
        return [
            "attributes" => [
                "urn:mace:dir:attribute-def:eduPersonEntitlement" => ["user_aff1@test.sram.surf.nl", "user_aff2@test.sram.surf.nl"],
                "urn:mace:dir:attribute-def:eduPersonPrincipalName" => ["test_user@test.sram.surf.nl"],
                "urn:mace:dir:attribute-def:uid" => ["test_user"],
                "urn:oid:1.3.6.1.4.1.24552.500.1.1.1.13" => ["ssh_key1", "ssh_key2"],
            ],
        ];
    }

    public function prepareAttributesResponse(array $attributes): void
    {
        $this->attributes = $attributes;
        $this->save();
    }

    public function getPreparedAttributesResponse(): array
    {
        return $this->dataStore->load()['attributes'];
    }

    private function save()
    {
        $this->dataStore->save([
            'authz' => $this->authz,
            'attributes' => $this->attributes,
        ]);
    }
}
