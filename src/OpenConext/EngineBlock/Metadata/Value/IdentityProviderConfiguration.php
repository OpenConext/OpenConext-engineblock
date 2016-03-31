<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Saml\EntitySet;
use OpenConext\Value\Serializable;

final class IdentityProviderConfiguration implements Serializable
{
    /**
     * @var EntityConfiguration
     */
    private $entityConfiguration;

    /**
     * @var EntitySet
     */
    private $serviceProvidersWithoutConsent;

    /**
     * @var GuestQualifier
     */
    private $guestQualifier;

    public function __construct(
        EntityConfiguration $samlEntityConfiguration,
        EntitySet $serviceProvidersWithoutConsent,
        GuestQualifier $guestQualifier
    ) {
        $this->entityConfiguration            = $samlEntityConfiguration;
        $this->serviceProvidersWithoutConsent = $serviceProvidersWithoutConsent;
        $this->guestQualifier                 = $guestQualifier;
    }

    /**
     * @return EntityConfiguration
     */
    public function getEntityConfiguration()
    {
        return $this->entityConfiguration;
    }

    /**
     * @return EntitySet
     */
    public function getServiceProvidersWithoutConsent()
    {
        return $this->serviceProvidersWithoutConsent;
    }

    /**
     * @return GuestQualifier
     */
    public function getGuestQualifier()
    {
        return $this->guestQualifier;
    }

    public function equals(IdentityProviderConfiguration $other)
    {
        return $this->entityConfiguration->equals($other->entityConfiguration)
                && $this->serviceProvidersWithoutConsent->equals($other->serviceProvidersWithoutConsent)
                && $this->guestQualifier->equals($other->guestQualifier);
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist(
            $data,
            ['entity_configuration', 'service_providers_without_consent', 'guest_qualifier']
        );

        return new self(
            EntityConfiguration::deserialize($data['entity_configuration']),
            EntitySet::deserialize($data['service_providers_without_consent']),
            GuestQualifier::deserialize($data['guest_qualifier'])
        );
    }

    public function serialize()
    {
        return [
            'entity_configuration'              => $this->entityConfiguration->serialize(),
            'service_providers_without_consent' => $this->serviceProvidersWithoutConsent->serialize(),
            'guest_qualifier'                   => $this->guestQualifier->serialize()
        ];
    }

    public function __toString()
    {
        return sprintf(
            'IdentityProviderConfiguration(%s, %s, %s)',
            $this->entityConfiguration,
            'ServiceProvidersWithoutConsent=' . $this->serviceProvidersWithoutConsent,
            $this->guestQualifier
        );
    }
}
