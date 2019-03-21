<?php

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
                'single_logout_service'   => [
                    'binding'  => $identityProvider->singleLogoutService->binding,
                    'location' => $identityProvider->singleLogoutService->location,
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
                    'en' => $identityProvider->descriptionEn,
                    'nl' => $identityProvider->descriptionNl,
                    'pt' => $identityProvider->descriptionPt,
                ],
                'display_name'            => [
                    'en' => $identityProvider->displayNameEn,
                    'nl' => $identityProvider->displayNameNl,
                    'pt' => $identityProvider->displayNamePt,
                ],
                'logo'                    => [
                    'height' => $identityProvider->logo->height,
                    'width'  => $identityProvider->logo->width,
                    'url'    => $identityProvider->logo->url,
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
