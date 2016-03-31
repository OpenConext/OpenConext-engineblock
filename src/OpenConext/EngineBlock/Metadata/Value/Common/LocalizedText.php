<?php

namespace OpenConext\EngineBlock\Metadata\Value\Common;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Serializable;

/**
 * Value object that represents a translatable textual element for a SAMLentity, akin to the formal
 * LocalizedName and LocalizedUri (common metadata types). This allows for instance the configuration
 * of localized descriptions for services.
 */
final class LocalizedText implements Serializable
{
    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $language;

    /**
     * @param string $text
     * @param string $language
     */
    public function __construct($text, $language)
    {
        Assertion::nonEmptyString($text, 'text');
        Assertion::nonEmptyString($language, 'language');

        $this->text = $text;
        $this->language = $language;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    public function equals(LocalizedText $other)
    {
        return $this->text === $other->text && $this->language === $other->language;
    }

    public static function deserialize($data)
    {
        Assertion::isArray($data);
        Assertion::keysExist($data, ['text', 'language']);

        return new self($data['text'], $data['language']);
    }

    public function serialize()
    {
        return [
            'text'     => $this->text,
            'language' => $this->language
        ];
    }

    public function __toString()
    {
        return sprintf(
            'LocalizedText(text="%s", language=%s)',
            $this->text,
            $this->language
        );
    }
}
