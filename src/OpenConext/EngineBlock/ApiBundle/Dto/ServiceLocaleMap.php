<?php

namespace OpenConext\EngineBlock\ApiBundle\Dto;

use OpenConext\Component\EngineBlockMetadata\ContactPerson;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

final class ServiceLocaleMap
{
    const CONTACT_TYPE_SUPPORT = 'support';

    /**
     * @var Service[] Service descriptors indexed by locale
     */
    public $services = array();

    /**
     * @param ServiceProvider $serviceProvider
     * @return ServiceLocaleMap
     */
    public static function fromServiceProvider(ServiceProvider $serviceProvider)
    {
        $serviceEn = new Service();
        $serviceEn->locale = 'en';
        $serviceEn->displayName = $serviceProvider->displayNameEn;
        $serviceEn->eulaUrl = $serviceProvider->termsOfServiceUrl;
        $serviceEn->supportUrl = null;

        /** @var ContactPerson[] $supportContacts */
        $supportContacts = array_values(
            array_filter(
                $serviceProvider->contactPersons,
                function (ContactPerson $contact) {
                    return $contact->contactType === ServiceLocaleMap::CONTACT_TYPE_SUPPORT;
                }
            )
        );

        if (count($supportContacts) > 0) {
            $serviceEn->supportEmail = $supportContacts[0]->emailAddress;
        }

        $serviceNl = new Service();
        $serviceNl->locale = 'nl';
        $serviceNl->displayName = $serviceProvider->displayNameNl;
        $serviceNl->eulaUrl = $serviceProvider->termsOfServiceUrl;
        $serviceNl->supportUrl = null;

        /** @var ContactPerson[] $supportContacts */
        $supportContacts = array_values(
            array_filter(
                $serviceProvider->contactPersons,
                function (ContactPerson $contact) {
                    return $contact->contactType === ServiceLocaleMap::CONTACT_TYPE_SUPPORT;
                }
            )
        );

        if (count($supportContacts) > 0) {
            $serviceNl->supportEmail = $supportContacts[0]->emailAddress;
        }

        return new ServiceLocaleMap(array($serviceEn, $serviceNl));
    }

    /**
     * @param Service[] $services
     */
    public function __construct(array $services)
    {
        foreach ($services as $service) {
            $this->initialiseWith($service);
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array_map(
            function (Service $service) {
                return $service->jsonSerialize();
            },
            $this->services
        );
    }

    private function initialiseWith(Service $service)
    {
        $this->services[$service->locale] = $service;
    }
}
