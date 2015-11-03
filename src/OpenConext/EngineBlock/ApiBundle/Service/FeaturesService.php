<?php

namespace OpenConext\EngineBlock\ApiBundle\Service;

use OpenConext\EngineBlock\ApiBundle\Exception\InvalidArgumentException;

class FeaturesService
{
    /**
     * @var bool
     */
    private $metadataPushEnabled;

    /**
     * @param bool $metadataPushEnabled
     */
    public function __construct($metadataPushEnabled)
    {
        if (!is_bool($metadataPushEnabled)) {
            throw InvalidArgumentException::invalidType('bool', 'metadataPushEnabled', $metadataPushEnabled);
        }

        $this->metadataPushEnabled = $metadataPushEnabled;
    }

    /**
     * @return bool
     */
    public function metadataPushIsEnabled()
    {
        return $this->metadataPushEnabled;
    }
}
