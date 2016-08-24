<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\Value\Serializable;

/**
 * Keywords are a list of words per language that are, amongst other places, used in the WAYF screen
 */
final class Keywords implements Serializable
{
    /**
     * @var LocalizedKeywords[]
     */
    private $localizedKeywords;

    /**
     * Builds a Keywords object from an array indexed by locale, with for each locale its keywords as space separated
     * string as its value, e.g. ['nl' => 'een woord', 'en' => 'some keyword']
     */
    public static function fromDefinition(array $definition)
    {
        Assertion::allNonEmptyString(array_keys($definition), 'locales (keys of definition)');
        Assertion::allNonEmptyString($definition, 'keywords (value of definition');

        $localizedKeywords = [];
        foreach ($definition as $locale => $keywordsAsString) {
            $localizedKeywords[] = new LocalizedKeywords($locale, explode(' ', $keywordsAsString));
        }

        return new self($localizedKeywords);
    }

    /**
     * @param LocalizedKeywords[] $localizedKeywords
     */
    public function __construct($localizedKeywords)
    {
        Assertion::allIsInstanceOf($localizedKeywords, LocalizedKeywords::class);

        $this->localizedKeywords = $localizedKeywords;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function hasKeywordsForLocale($locale)
    {
        Assertion::nonEmptyString($locale, 'locale');

        foreach ($this->localizedKeywords as $keywords) {
            if ($keywords->getLocale() === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $locale
     * @return LocalizedKeywords
     */
    public function getKeywordsForLocale($locale)
    {
        Assertion::nonEmptyString($locale, 'locale');

        if (!$this->hasKeywordsForLocale($locale)) {
            throw new LogicException(
                'Cannot get keywords for locale that has no keywords defined, did you verify they exist with '
                . 'hasKeywordsForLocale?'
            );
        }

        foreach ($this->localizedKeywords as $keywords) {
            if ($keywords->getLocale() === $locale) {
                return $keywords;
            }
        }
    }

    public function equals(Keywords $other)
    {
        if (count($this->localizedKeywords) !== count($other->localizedKeywords)) {
            return false;
        }

        foreach ($this->localizedKeywords as $index => $localizedKeywords) {
            if (!$localizedKeywords->equals($other->localizedKeywords[$index])) {
                return false;
            }
        }

        return true;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);

        $localizedKeywords = array_map(function ($serializedLocalizedKeywords) {
            return LocalizedKeywords::deserialize($serializedLocalizedKeywords);
        }, $data);

        return new self($localizedKeywords);
    }

    public function serialize()
    {
        return array_map(function (LocalizedKeywords $localizedKeywords) {
            return $localizedKeywords->serialize();
        }, $this->localizedKeywords);
    }

    public function __toString()
    {
        return sprintf('Keywords[%s]', implode(', ', $this->localizedKeywords));
    }
}
