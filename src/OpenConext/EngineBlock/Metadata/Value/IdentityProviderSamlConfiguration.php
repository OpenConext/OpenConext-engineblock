<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Saml\Metadata\ShibbolethMetadataScopeList;
use OpenConext\Value\Serializable;

class IdentityProviderSamlConfiguration implements Serializable
{
    /**
     * @var EntitySamlConfiguration
     */
    private $entitySamlConfiguration;

    /**
     * @var SingleSignOnServices
     */
    private $singleSignOnServices;

    /**
     * @var ShibbolethMetadataScopeList
     */
    private $shibbolethMetadataScopeList;

    public function __construct(
        EntitySamlConfiguration $entitySamlConfiguration,
        SingleSignOnServices $singleSignOnServices,
        ShibbolethMetadataScopeList $shibbolethMetadataScopeList
    ) {
        $this->entitySamlConfiguration     = $entitySamlConfiguration;
        $this->singleSignOnServices        = $singleSignOnServices;
        $this->shibbolethMetadataScopeList = $shibbolethMetadataScopeList;
    }

    /**
     * @return EntitySamlConfiguration
     */
    public function getEntitySamlConfiguration()
    {
        return $this->entitySamlConfiguration;
    }

    /**
     * @return SingleSignOnServices
     */
    public function getSingleSignOnServices()
    {
        return $this->singleSignOnServices;
    }

    /**
     * @return ShibbolethMetadataScopeList
     */
    public function getShibbolethMetadataScopeList()
    {
        return $this->shibbolethMetadataScopeList;
    }

    /**
     * @param IdentityProviderSamlConfiguration $other
     * @return bool
     */
    public function equals(IdentityProviderSamlConfiguration $other)
    {
        return $this->entitySamlConfiguration->equals($other->entitySamlConfiguration)
                && $this->singleSignOnServices->equals($other->singleSignOnServices)
                && $this->shibbolethMetadataScopeList->equals($other->shibbolethMetadataScopeList);
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, [
            'entity_saml_configuration',
            'single_sign_on_services',
            'shibboleth_metadata_scope_list'
        ]);

        return new self(
            EntitySamlConfiguration::deserialize($data['entity_saml_configuration']),
            SingleSignOnServices::deserialize($data['single_sign_on_services']),
            ShibbolethMetadataScopeList::deserialize($data['shibboleth_metadata_scope_list'])
        );
    }

    public function serialize()
    {
        return [
            'entity_saml_configuration'      => $this->entitySamlConfiguration->serialize(),
            'single_sign_on_services'        => $this->singleSignOnServices->serialize(),
            'shibboleth_metadata_scope_list' => $this->shibbolethMetadataScopeList->serialize()
        ];
    }

    public function __toString()
    {
        return sprintf(
            'IdentityProviderSamlConfiguration(%s, %s, %s)',
            $this->entitySamlConfiguration,
            $this->singleSignOnServices,
            $this->shibbolethMetadataScopeList
        );
    }
}
