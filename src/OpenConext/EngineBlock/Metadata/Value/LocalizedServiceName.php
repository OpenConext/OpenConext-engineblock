<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\Value\Saml\Metadata\Common\LocalizedName;
use OpenConext\Value\Serializable;

final class LocalizedServiceName implements Serializable
{
    /**
     * @var LocalizedName[]
     */
    private $serviceNames;

    /**
     * @param LocalizedName[] $serviceNames
     */
    public function __construct(array $serviceNames)
    {
        Assertion::allIsInstanceOf($serviceNames, LocalizedName::class);

        $this->serviceNames = $serviceNames;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function hasServiceNameForLocale($locale)
    {
        Assertion::nonEmptyString($locale, 'locale');

        foreach ($this->serviceNames as $serviceName) {
            if ($serviceName->getLanguage() === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $locale
     * @return LocalizedName
     */
    public function getServiceNameForLocale($locale)
    {
        Assertion::nonEmptyString($locale, 'locale');

        if (!$this->hasServiceNameForLocale($locale)) {
            throw new LogicException(sprintf(
                'Cannot get service name for locale %s, did you verify it is available with hasServiceNameForLocale?',
                $locale
            ));
        }

        foreach ($this->serviceNames as $serviceName) {
            if ($serviceName->getLanguage() === $locale) {
                return $serviceName;
            }
        }
    }

    /**
     * @param LocalizedServiceName $other
     * @return bool
     */
    public function equals(LocalizedServiceName $other)
    {
        if (count($this->serviceNames) !== count($other->serviceNames)) {
            return false;
        }

        foreach ($this->serviceNames as $serviceName) {
            if (!$other->hasServiceNameForLocale($serviceName->getLanguage())
                || !$serviceName->equals($other->getServiceNameForLocale($serviceName->getLanguage()))
            ) {
                return false;
            }
        }

        return true;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);

        $serviceNames = array_map(function ($serviceName) {
            return LocalizedName::deserialize($serviceName);
        }, $data);

        return new self($serviceNames);
    }

    public function serialize()
    {
        return array_map(function (LocalizedName $serviceName) {
            return $serviceName->serialize();
        }, $this->serviceNames);
    }

    public function __toString()
    {
        return sprintf('LocalizedServiceName[%s]', implode(', ', $this->serviceNames));
    }
}
