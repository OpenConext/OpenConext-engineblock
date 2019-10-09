<?php
declare(strict_types=1);

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

namespace OpenConext\EngineBlock\Xml\ValueObjects;

use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProvider;

class ServiceProviderMetadata
{
    /**
     * The number of seconds a Metadata document is deemed valid
     */
    const METADATA_EXPIRATION_TIME = 86400;
    /**
     * @var ServiceProviderEntityInterface
     */
    private $entity;

    public function __construct(ServiceProviderEntityInterface $entity)
    {
        $this->entity = $entity;
    }

    public function getEntityId(): string
    {
        return $this->entity->getEntityId();
    }

    public function getValidUntil()
    {
        $timeProvider = new TimeProvider();
        return $timeProvider->timestamp(self::METADATA_EXPIRATION_TIME);
    }

    public function getAssertionConsumerServices() : IndexedService
    {
        return reset($this->entity->getAssertionConsumerServices());
    }

    public function getContactPersons()
    {
        $administrative = new ContactPerson('administrative');
        $administrative->givenName = 'To';
        $administrative->surName = 'Do';
        $administrative->emailAddress = 'todo@todo.com';

        $technical = new ContactPerson('technical');
        $technical->emailAddress = 'todo@todo.com';
        $technical->givenName = 'To';
        $technical->surName = 'Do';

        $support = new ContactPerson('support');
        $support->emailAddress = 'todo@todo.com';
        $support->givenName = 'To';
        $support->surName = 'Do';

        return [
            $administrative,
            $technical,
            $support
        ];
    }

    public function getUiInfo()
    {
        return [
            'nameNl' => 'SuiteName + EngineBlock',
            'nameEn' => 'SuiteName + EngineBlock',
            'descriptionNl' => 'SuiteName + EngineBlock',
            'descriptionEn' => 'SuiteName + EngineBlock',
            'organization' => 'SuitName',
            'organizationSupportUrl' => 'supportUrl',
            'logo' => [
                'url' => 'https://www.google.com',
                'width' => 200,
                'height' => 200,
            ]
        ];
    }

    public function getPublicKeys(): array
    {
        $keys = [];
        foreach ($this->entity->getCertificates() as $certificate) {
            $pem = $certificate->toCertData();
            $keys[$pem] = $pem;
        }
        return $keys;
    }
}
