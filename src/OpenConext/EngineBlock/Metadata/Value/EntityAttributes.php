<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class EntityAttributes implements Serializable
{
    /**
     * @var LocalizedServiceName
     */
    private $localizedServiceName;

    /**
     * @var LocalizedDescription
     */
    private $localizedDescription;

    /**
     * @var Logo
     */
    private $logo;

    public function __construct(
        LocalizedServiceName $localizedServiceName,
        LocalizedDescription $localizedDescription,
        Logo $logo
    ) {
        $this->localizedServiceName = $localizedServiceName;
        $this->localizedDescription = $localizedDescription;
        $this->logo = $logo;
    }

    /**
     * @return LocalizedServiceName
     */
    public function getServiceName()
    {
        return $this->localizedServiceName;
    }

    /**
     * @return LocalizedDescription
     */
    public function getDescription()
    {
        return $this->localizedDescription;
    }

    /**
     * @return Logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param EntityAttributes $other
     * @return bool
     */
    public function equals(EntityAttributes $other)
    {
        return $this->localizedServiceName->equals($other->localizedServiceName)
                && $this->localizedDescription->equals($other->localizedDescription)
                && $this->logo->equals($other->logo);
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, ['service_name', 'description', 'logo']);

        return new self(
            LocalizedServiceName::deserialize($data['service_name']),
            LocalizedDescription::deserialize($data['description']),
            Logo::deserialize($data['logo'])
        );
    }

    public function serialize()
    {
        return [
            'service_name' => $this->localizedServiceName->serialize(),
            'description' => $this->localizedDescription->serialize(),
            'logo' => $this->logo->serialize()
        ];
    }

    public function __toString()
    {
        return sprintf(
            'EntityAttributes(ServiceName=%s, Description=%s, Logo=%s)',
            $this->localizedServiceName,
            $this->localizedDescription,
            $this->logo
        );
    }
}
