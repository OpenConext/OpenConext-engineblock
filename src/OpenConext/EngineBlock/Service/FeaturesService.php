<?php

namespace OpenConext\EngineBlock\Service;

use OpenConext\EngineBlock\Assert\Assertion;

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
        $message = 'Expected boolean for value for "%s"';
        Assertion::boolean($metadataPushEnabled, sprintf($message, 'metadataPushEnabled') . ', "%s" given');
        Assertion::boolean($consentListingEnabled, sprintf($message, 'consentListingEnabled') . ', "%s" given');
        Assertion::boolean($metadataApiEnabled, sprintf($message, 'metadataApiEnabled') . ', "%s" given');

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
