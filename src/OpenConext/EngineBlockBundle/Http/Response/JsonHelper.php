<?php

namespace OpenConext\EngineBlockBundle\Http\Response;

use OpenConext\Component\EngineBlockMetadata\ContactPerson;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Service;
use OpenConext\Component\EngineBlockMetadata\ShibMdScope;

final class JsonHelper
{
    /**
     * @param IdentityProvider $identityProvider
     * @return string
     */
    public static function serializeIdentityProvider(IdentityProvider $identityProvider)
    {
        return json_encode(
            array(
                'entity_id'               => $identityProvider->entityId,
                'organization'            => array(
                    'en' => array(
                        'name'         => $identityProvider->organizationEn->name,
                        'display_name' => $identityProvider->organizationEn->displayName,
                        'url'          => $identityProvider->organizationEn->url,
                    ),
                    'nl' => array(
                        'name'         => $identityProvider->organizationNl->name,
                        'display_name' => $identityProvider->organizationNl->displayName,
                        'url'          => $identityProvider->organizationNl->url,
                    )
                ),
                'single_logout_service'   => array(
                    'binding'  => $identityProvider->singleLogoutService->binding,
                    'location' => $identityProvider->singleLogoutService->location,
                ),
                'contact_persons'         => array_map(
                    function (ContactPerson $contactPerson) {
                        return array(
                            'contact_type'  => $contactPerson->contactType,
                            'email_address' => $contactPerson->emailAddress,
                        );
                    },
                    $identityProvider->contactPersons
                ),
                'description'             => array(
                    'en' => $identityProvider->descriptionEn,
                    'nl' => $identityProvider->descriptionNl
                ),
                'display_name'            => array(
                    'en' => $identityProvider->displayNameEn,
                    'nl' => $identityProvider->displayNameNl,
                ),
                'logo'                    => array(
                    'height' => $identityProvider->logo->height,
                    'width'  => $identityProvider->logo->width,
                    'url'    => $identityProvider->logo->url,
                ),
                'name'                    => array(
                    'en' => $identityProvider->nameEn,
                    'nl' => $identityProvider->nameNl,
                ),
                'shib_md_scopes'          => array_map(
                    function (ShibMdScope $shibMdScope) {
                        return array(
                            'regexp'  => (bool) $shibMdScope->regexp,
                            'allowed' => $shibMdScope->allowed,
                        );
                    },
                    $identityProvider->shibMdScopes
                ),
                'single_sign_on_services' => array_map(
                    function (Service $service) {
                        return array(
                            'binding'  => $service->binding,
                            'location' => $service->location,
                        );
                    },
                    $identityProvider->singleSignOnServices
                ),
            )
        );
    }
}
