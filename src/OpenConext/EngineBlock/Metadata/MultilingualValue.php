<?php

namespace OpenConext\EngineBlock\Metadata;

use Assert\Assertion;

class MultilingualValue
{
    private $language;

    private $value;

    /**
     * MultilingualValue constructor.
     * @param string $value
     * @param string $language
     * @throws \Assert\AssertionFailedException
     */
    public function __construct($value, $language)
    {
        Assertion::string(
            $value,
            sprintf(
                'The \'value\' of a MultilingualValue should be a string. "%s" given',
                var_export($value, true)
            )
        );
        Assertion::string(
            $language,
            sprintf(
                'The \'language\' of a MultilingualValue should be a string. "%s" given',
                var_export($language, true)
            )
        );

        $this->value = $value;
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
