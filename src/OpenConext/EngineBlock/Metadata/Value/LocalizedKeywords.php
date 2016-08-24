<?php

namespace OpenConext\EngineBlock\Metadata\Value;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

final class LocalizedKeywords implements Serializable
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var string[]
     */
    private $keywords;

    /**
     * @param string   $locale
     * @param string[] $keywords
     */
    public function __construct($locale, array $keywords)
    {
        Assertion::nonEmptyString($locale, 'locale');
        Assertion::allString($keywords);

        $this->locale   = $locale;
        $this->keywords = $keywords;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string[]
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param LocalizedKeywords $other
     * @return bool
     */
    public function equals(LocalizedKeywords $other)
    {
        return $this->locale === $other->locale && $this->keywords === $other->keywords;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, ['locale', 'keywords']);

        return new self($data['locale'], $data['keywords']);
    }

    public function serialize()
    {
        return [
            'locale' => $this->locale,
            'keywords' => $this->keywords
        ];
    }

    public function __toString()
    {
        return sprintf(
            'LocalizedKeywords(locale=%s, keywords=["%s"])',
            $this->locale,
            implode('", "', $this->keywords)
        );
    }
}
