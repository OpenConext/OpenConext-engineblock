<?php declare(strict_types=1);
/**
 * Copyright 2010 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Entities\Decorator;

use OpenConext\EngineBlock\Entities\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use SAML2\Constants;

class IdentityProviderProxy extends AbstractIdentityProvider
{
    /**
     * @var X509KeyPair
     */
    private $keyPair;

    public function __construct(
        IdentityProviderEntityInterface $entity,
        X509KeyPair $keyPair
    ) {
        parent::__construct($entity);

        $this->keyPair = $keyPair;
    }

    public function getCertificates(): array
    {
        return [$this->keyPair->getCertificate()];
    }

    public function getSupportedNameIdFormats(): array
    {
        return [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];
    }
}
