<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class ServiceProviderSamlConfiguration implements Serializable
{
    /**
     * @var EntitySamlConfiguration
     */
    private $entitySamlConfiguration;
    /**
     * @var AssertionConsumerServices
     */
    private $assertionConsumerServices;

    public function __construct(
        EntitySamlConfiguration $entitySamlConfiguration,
        AssertionConsumerServices $assertionConsumerServices
    ) {
        $this->entitySamlConfiguration = $entitySamlConfiguration;
        $this->assertionConsumerServices = $assertionConsumerServices;
    }

    /**
     * @return EntitySamlConfiguration
     */
    public function getEntitySamlConfiguration()
    {
        return $this->entitySamlConfiguration;
    }

    /**
     * @return AssertionConsumerServices
     */
    public function getAssertionConsumerServices()
    {
        return $this->assertionConsumerServices;
    }

    /**
     * @param ServiceProviderSamlConfiguration $other
     * @return bool
     */
    public function equals(ServiceProviderSamlConfiguration $other)
    {
        return $this->entitySamlConfiguration->equals($other->entitySamlConfiguration)
                && $this->assertionConsumerServices->equals($other->assertionConsumerServices);
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, ['entity_saml_configuration', 'assertion_consumer_services']);

        return new self(
            EntitySamlConfiguration::deserialize($data['entity_saml_configuration']),
            AssertionConsumerServices::deserialize($data['assertion_consumer_services'])
        );
    }

    public function serialize()
    {
        return [
            'entity_saml_configuration'   => $this->entitySamlConfiguration->serialize(),
            'assertion_consumer_services' => $this->assertionConsumerServices->serialize()
        ];
    }

    public function __toString()
    {
        return sprintf(
            'ServiceProviderSamlConfiguration(%s, %s)',
            $this->entitySamlConfiguration,
            $this->assertionConsumerServices
        );
    }
}
