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

namespace OpenConext\EngineBlock\Metadata\Factory\Decorator;

use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;

/**
 * This decoration is used to represent EngineBlock in it's Stepup role when EngineBlock is doing a stepup callout
 * It will make sure the right acs location is used to consume the response back to EB.
 */
class ServiceProviderStepup extends AbstractServiceProvider
{
    /**
     * @var X509KeyPair
     */
    private $keyPair;
    /**
     * @var UrlProvider
     */
    private $urlProvider;

    public function __construct(
        ServiceProviderEntityInterface $entity,
        X509KeyPair $keyPair,
        UrlProvider $urlProvider
    ) {
        parent::__construct($entity);

        $this->keyPair = $keyPair;
        $this->urlProvider = $urlProvider;
    }

    public function getCertificates(): array
    {
        return [$this->keyPair->getCertificate()];
    }

    /**
     * @return string[]
     */
    public function getSupportedNameIdFormats(): array
    {
        return [];
    }

    /**
     * @return IndexedService[]
     */
    public function getAssertionConsumerServices(): array
    {
        $acsLocation = $this->urlProvider->getUrl('authentication_stepup_consume_assertion', false, null, null);
        return [new IndexedService($acsLocation, Constants::BINDING_HTTP_POST, 0)];
    }
}
