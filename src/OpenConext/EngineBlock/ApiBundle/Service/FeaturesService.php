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
     * @var bool
     */
    private $consentListingEnabled;

    /**
     * @param bool $metadataPushEnabled
     * @param bool $consentListingEnabled
     */
    public function __construct($metadataPushEnabled, $consentListingEnabled)
    {
        if (!is_bool($metadataPushEnabled)) {
            throw InvalidArgumentException::invalidType('bool', 'metadataPushEnabled', $metadataPushEnabled);
        }

        if (!is_bool($consentListingEnabled)) {
            throw InvalidArgumentException::invalidType('bool', 'consentListingEnabled', $consentListingEnabled);
        }

        $this->metadataPushEnabled = $metadataPushEnabled;
        $this->consentListingEnabled = $consentListingEnabled;
    }

    /**
     * @return bool
     */
    public function metadataPushIsEnabled()
    {
        return $this->metadataPushEnabled;
    }

    /**
     * @return bool
     */
    public function consentListingIsEnabled()
    {
        return $this->consentListingEnabled;
    }
}
