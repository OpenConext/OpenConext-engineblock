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
     * @var bool
     */
    private $metadataApiEnabled;

    /**
     * @param bool $metadataPushEnabled
     * @param bool $consentListingEnabled
     * @param bool $metadataApiEnabled
     */
    public function __construct($metadataPushEnabled, $consentListingEnabled, $metadataApiEnabled)
    {
        if (!is_bool($metadataPushEnabled)) {
            throw InvalidArgumentException::invalidType('bool', 'metadataPushEnabled', $metadataPushEnabled);
        }

        if (!is_bool($consentListingEnabled)) {
            throw InvalidArgumentException::invalidType('bool', 'consentListingEnabled', $consentListingEnabled);
        }

        if (!is_bool($metadataApiEnabled)) {
            throw InvalidArgumentException::invalidType('bool', 'metadataApiEnabled', $metadataApiEnabled);
        }

        $this->metadataPushEnabled   = $metadataPushEnabled;
        $this->consentListingEnabled = $consentListingEnabled;
        $this->metadataApiEnabled    = $metadataApiEnabled;
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

    /**
     * @return bool
     */
    public function metadataApiIsEnabled()
    {
        return $this->metadataApiEnabled;
    }
}
