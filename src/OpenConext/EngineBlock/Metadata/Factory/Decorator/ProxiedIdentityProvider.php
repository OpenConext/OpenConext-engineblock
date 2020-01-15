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

use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;

/**
 * Represents an IdentityProvider that EngineBlock proxies
 *
 * IdP metadata is rendered for these IdPs in EngineBlocks IdPs metadata document.
 */
class ProxiedIdentityProvider extends AbstractIdentityProvider
{
    /**
     * @var EngineBlockConfiguration
     */
    private $engineBlockConfiguration;

    /**
     * @var X509KeyPair
     */
    private $keyPair;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    public function __construct(
        IdentityProviderEntityInterface $entity,
        EngineBlockConfiguration $engineBlockConfiguration,
        X509KeyPair $keyPair,
        UrlProvider $urlProvider
    ) {
        parent::__construct($entity);
        $this->engineBlockConfiguration = $engineBlockConfiguration;
        $this->keyPair = $keyPair;
        $this->urlProvider = $urlProvider;
    }

    public function getSingleLogoutService(): ?Service
    {
        if (is_null($this->entity->getSingleLogoutService())) {
            return null;
        }
        $sloService = $this->entity->getSingleLogoutService();
        return new Service($sloService->location, $sloService->binding);
    }

    public function getCertificates(): array
    {
        return [
            $this->keyPair->getCertificate(),
        ];
    }

    public function getSupportedNameIdFormats(): array
    {
        return [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];
    }

    /**
     * The configured EB contact persons are displayed for IdP entities that EngineBlock proxies.
     * @return ContactPerson[]
     */
    public function getContactPersons(): array
    {
        return $this->engineBlockConfiguration->getContactPersons();
    }

    public function getSingleSignOnServices(): array
    {
        $ssoLocation = $this->urlProvider->getUrl('authentication_idp_sso', false, null, $this->getEntityId());
        return [new Service($ssoLocation, Constants::BINDING_HTTP_REDIRECT)];
    }

    /**
     * Best effort to show a Dutch display name.
     *
     * Buisiness rule:
     * displayname:nl, name:nl, name:en
     */
    public function getDisplayNameNl(): string
    {
        if (empty($this->entity->getDisplayNameNl())) {
            if (!empty($this->entity->getNameNl())) {
                return $this->entity->getNameNl();
            }
            return $this->entity->getNameEn();
        }
        return $this->entity->getDisplayNameNl();
    }

    /**
     * Best effort to show an English display name.
     *
     * Buisiness rule:
     * displayname:en, name:en
     */
    public function getDisplayNameEn(): string
    {
        if (empty($this->entity->getDisplayNameEn())) {
            return $this->entity->getNameEn();
        }
        return $this->entity->getDisplayNameEn();
    }
}
