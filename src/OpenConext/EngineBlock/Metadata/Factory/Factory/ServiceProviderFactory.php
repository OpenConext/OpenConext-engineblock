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

namespace OpenConext\EngineBlock\Metadata\Factory\Factory;

use EngineBlock_Attributes_Metadata as AttributesMetadata;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\ServiceProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockServiceProviderMetadata;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\ServiceProviderProxy;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use SAML2\Constants;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This factory is used for instantiating an entity with the required adapters and/or decorators set.
 * It also makes sure that static, internally used, entities can be generated without the use of the database.
 */
class ServiceProviderFactory
{
    /**
     * @var AttributesMetadata
     */
    private $attributes;

    /**
     * @var KeyPairFactory
     */
    private $keyPairFactory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Logo
     */
    private $logo;

    /**
     * @var string
     */
    private $supportUrl;

    /**
     * @var string
     */
    private $supportMail;

    /**
     * @var string
     */
    private $description;

    public function __construct(
        AttributesMetadata $attributes,
        KeyPairFactory $keyPairFactory,
        TranslatorInterface $translator,
        string $description,
        string $supportUrl,
        string $supportMail,
        string $logoUrl,
        int $logoWidth,
        int $logoHeight
    ) {
        $this->attributes = $attributes;
        $this->keyPairFactory = $keyPairFactory;
        $this->translator = $translator;
        $this->description = $description;
        $this->supportUrl = $supportUrl;
        $this->supportMail = $supportMail;
        // A logo VO is created during construction time
        $this->logo = new Logo($logoUrl);
        $this->logo->width = $logoWidth;
        $this->logo->height = $logoHeight;
    }

    public function createEntityFromEntity(ServiceProvider $entity): ServiceProviderEntityInterface
    {
        return new ServiceProviderEntity($entity);
    }

    public function createEngineBlockEntityFrom(
        string $entityId,
        string $acsLocation,
        string $keyId
    ): ServiceProviderEntityInterface {
        $entity = $this->buildServiceProviderEntity($entityId, $acsLocation, $keyId);

        // Obtain the suite name (set in translations) this is used to create several other entity properties
        // (name, description, and so forth)
        $suiteName = $this->translator->trans('suite_name');

        // Create the contact person data for the EB SP entity
        $administrative = ContactPerson::from('administrative', $suiteName, 'Support', $this->supportMail);
        $technical = ContactPerson::from('technical', $suiteName, 'Support', $this->supportMail);
        $support = ContactPerson::from('support', $suiteName, 'Support', $this->supportMail);

        // Load the additional EB SP metadata onto the entity
        $entity->nameEn = $suiteName . ' EngineBlock';
        $entity->nameNl = $suiteName . ' EngineBlock';
        $entity->descriptionEn = $this->description;
        $entity->descriptionNl = $this->description;
        $entity->organizationEn = new Organization($suiteName, $suiteName, $this->supportUrl);
        $entity->contactPersons = [
            $administrative,
            $technical,
            $support,
        ];

        $entity->logo = $this->logo;

        return new EngineBlockServiceProviderMetadata($this->createEntityFromEntity($entity));
    }

    public function createProxyFromEntity(
        ServiceProvider $entity,
        X509KeyPair $proxyKeyPair,
        Service $consentService
    ): ServiceProviderEntityInterface {
        return new ServiceProviderProxy(
            $this->createEntityFromEntity($entity),
            $proxyKeyPair,
            $this->attributes,
            $consentService
        );
    }

    public function createMinimalEntity(
        string $entityId,
        string $acsLocation,
        string $keyId,
        string $acsBindingMethod = Constants::BINDING_HTTP_POST
    ): ServiceProviderEntityInterface {
        $entity = $this->buildServiceProviderEntity($entityId, $acsLocation, $keyId, $acsBindingMethod);

        return $this->createEntityFromEntity($entity);
    }

    private function buildServiceProviderEntity(
        string $entityId,
        string $acsLocation,
        string $keyId,
        string $acsBindingMethod = Constants::BINDING_HTTP_POST
    ): ServiceProvider {
        $entity = new ServiceProvider($entityId);
        $entity->certificates[] = $this->keyPairFactory->buildFromIdentifier($keyId)->getCertificate();
        $entity->assertionConsumerServices[] = new IndexedService(
            $acsLocation,
            $acsBindingMethod,
            0
        );

        return $entity;
    }
}
