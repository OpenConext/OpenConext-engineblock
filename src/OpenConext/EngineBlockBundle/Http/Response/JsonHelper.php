<?php

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

namespace OpenConext\EngineBlockBundle\Http\Response;

use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;

final class JsonHelper
{
    /**
     * @param IdentityProvider $identityProvider
     * @return string
     */
    public static function serializeIdentityProvider(IdentityProvider $identityProvider)
    {
        return json_encode(
            [
                'entity_id'               => $identityProvider->entityId,
                'organization'            => [
                    'en' => [
                        'name'         => $identityProvider->organizationEn->name,
                        'display_name' => $identityProvider->organizationEn->displayName,
                        'url'          => $identityProvider->organizationEn->url,
                    ],
                    'nl' => [
                        'name'         => $identityProvider->organizationNl->name,
                        'display_name' => $identityProvider->organizationNl->displayName,
                        'url'          => $identityProvider->organizationNl->url,
                    ],
                    'pt' => [
                        'name'         => $identityProvider->organizationPt->name,
                        'display_name' => $identityProvider->organizationPt->displayName,
                        'url'          => $identityProvider->organizationPt->url,
                    ]
                ],
                'contact_persons'         => array_map(
                    function (ContactPerson $contactPerson) {
                        return [
                            'contact_type'  => $contactPerson->contactType,
                            'email_address' => $contactPerson->emailAddress,
                            'telephone_number' => $contactPerson->telephoneNumber,
                        ];
                    },
                    $identityProvider->contactPersons
                ),
                'description'             => [
                    'en' => $identityProvider->getMdui()->getDescriptionOrNull('en'),
                    'nl' => $identityProvider->getMdui()->getDescriptionOrNull('nl'),
                    'pt' => $identityProvider->getMdui()->getDescriptionOrNull('pt'),
                ],
                'display_name'            => [
                    'en' => $identityProvider->getMdui()->getDisplayNameOrNull('en'),
                    'nl' => $identityProvider->getMdui()->getDisplayNameOrNull('nl'),
                    'pt' => $identityProvider->getMdui()->getDisplayNameOrNull('pt'),
                ],
                'logo'                    => [
                    'height' => $identityProvider->getMdui()->getLogo()->height,
                    'width'  => $identityProvider->getMdui()->getLogo()->width,
                    'url'    => $identityProvider->getMdui()->getLogo()->url,
                ],
                'name'                    => [
                    'en' => $identityProvider->nameEn,
                    'nl' => $identityProvider->nameNl,
                    'pt' => $identityProvider->namePt,
                ],
                'shib_md_scopes'          => array_map(
                    function (ShibMdScope $shibMdScope) {
                        return [
                            'regexp'  => (bool) $shibMdScope->regexp,
                            'allowed' => $shibMdScope->allowed,
                        ];
                    },
                    $identityProvider->shibMdScopes
                ),
                'single_sign_on_services' => array_map(
                    function (Service $service) {
                        return [
                            'binding'  => $service->binding,
                            'location' => $service->location,
                        ];
                    },
                    $identityProvider->singleSignOnServices
                ),
            ]
        );
    }
}
