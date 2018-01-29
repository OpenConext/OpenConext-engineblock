<?php

namespace OpenConext\EngineBlock\Metadata;

/**
 * Class RequestedAttribute
 * @package OpenConext\EngineBlock\Metadata
 */
class RequestedAttribute
{
    const NAME_FORMAT_URI = 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $nameFormat = self::NAME_FORMAT_URI;

    /**
     * @var null|bool
     */
    public $required = null;

    /**
     * @param $name
     * @param bool $isRequired
     * @param string $nameFormat
     */
    public function __construct($name, $isRequired = false, $nameFormat = self::NAME_FORMAT_URI)
    {
        $this->name = $name;
        $this->nameFormat = $nameFormat;
        $this->required = $isRequired;
    }
}
