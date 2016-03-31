<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\Value\Saml\Metadata\Common\LocalizedUri;
use OpenConext\Value\Serializable;

final class LocalizedSupportUrl implements Serializable
{
    /**
     * @var LocalizedUri[]
     */
    private $supportUrls;

    /**
     * @param LocalizedUri[] $supportUrls
     */
    public function __construct(array $supportUrls)
    {
        Assertion::allIsInstanceOf($supportUrls, LocalizedUri::class);

        $this->supportUrls = $supportUrls;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function hasSupportUrlForLocale($locale)
    {
        Assertion::nonEmptyString($locale, 'locale');

        foreach ($this->supportUrls as $supportUrl) {
            if ($supportUrl->getLanguage() === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $locale
     * @return LocalizedUri
     */
    public function getSupportUrlForLocale($locale)
    {
        Assertion::nonEmptyString($locale, 'locale');

        if (!$this->hasSupportUrlForLocale($locale)) {
            throw new LogicException(sprintf(
                'Cannot get support url for locale %s, did you verify it is available with hasSupportUrlForLocale?',
                $locale
            ));
        }

        foreach ($this->supportUrls as $supportUrl) {
            if ($supportUrl->getLanguage() === $locale) {
                return $supportUrl;
            }
        }
    }

    /**
     * @param LocalizedSupportUrl $other
     * @return bool
     */
    public function equals(LocalizedSupportUrl $other)
    {
        if (count($this->supportUrls) !== count($other->supportUrls)) {
            return false;
        }

        foreach ($this->supportUrls as $supportUrl) {
            if (!$other->hasSupportUrlForLocale($supportUrl->getLanguage())
                || !$supportUrl->equals($other->getSupportUrlForLocale($supportUrl->getLanguage()))
            ) {
                return false;
            }
        }

        return true;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);

        $supportUrls = array_map(function ($supportUrl) {
            return LocalizedUri::deserialize($supportUrl);
        }, $data);

        return new self($supportUrls);
    }

    public function serialize()
    {
        return array_map(function (LocalizedUri $localizedUri) {
            return $localizedUri->serialize();
        }, $this->supportUrls);
    }

    public function __toString()
    {
        return sprintf('LocalizedSupportUrl[%s]', implode(', ', $this->supportUrls));
    }
}
