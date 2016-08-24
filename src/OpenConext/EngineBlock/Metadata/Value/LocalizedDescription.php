<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\EngineBlock\Metadata\Value\Common\LocalizedText;
use OpenConext\Value\Serializable;

final class LocalizedDescription implements Serializable
{
    /**
     * @var LocalizedText[]
     */
    private $descriptions;

    /**
     * @param LocalizedText[] $descriptions
     */
    public function __construct(array $descriptions)
    {
        Assertion::allIsInstanceOf($descriptions, LocalizedText::class);

        $this->descriptions = $descriptions;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function hasDescriptionForLocale($locale)
    {
        Assertion::nonEmptyString($locale, 'locale');

        foreach ($this->descriptions as $description) {
            if ($description->getLanguage() === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $locale
     * @return LocalizedText
     */
    public function getDescriptionForLocale($locale)
    {
        Assertion::nonEmptyString($locale, 'locale');

        if (!$this->hasDescriptionForLocale($locale)) {
            throw new LogicException(sprintf(
                'Cannot get description for locale %s, did you verify it is available with hasDescriptionForLocale?',
                $locale
            ));
        }

        foreach ($this->descriptions as $description) {
            if ($description->getLanguage() === $locale) {
                return $description;
            }
        }
    }

    /**
     * @param LocalizedDescription $other
     * @return bool
     */
    public function equals(LocalizedDescription $other)
    {
        if (count($this->descriptions) !== count($other->descriptions)) {
            return false;
        }

        foreach ($this->descriptions as $description) {
            if (!$other->hasDescriptionForLocale($description->getLanguage())
                || !$description->equals($other->getDescriptionForLocale($description->getLanguage()))
            ) {
                return false;
            }
        }

        return true;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);

        $descriptions = array_map(function ($description) {
            return LocalizedText::deserialize($description);
        }, $data);

        return new self($descriptions);
    }

    public function serialize()
    {
        return array_map(function (LocalizedText $localizedText) {
            return $localizedText->serialize();
        }, $this->descriptions);
    }

    public function __toString()
    {
        return sprintf('LocalizedDescription[%s]', implode(', ', $this->descriptions));
    }
}
