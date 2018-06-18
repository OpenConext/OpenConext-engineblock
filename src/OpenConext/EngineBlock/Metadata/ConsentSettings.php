<?php

namespace OpenConext\EngineBlock\Metadata;

/**
 * Value object for IDP consent settings.
 *
 * @package OpenConext\EngineBlock\Metadata
 */
class ConsentSettings implements \JsonSerializable
{
    const CONSENT_DISABLED = 'no_consent';
    const CONSENT_MINIMAL = 'minimal_consent';
    const CONSENT_DEFAULT = 'default_consent';

    /**
     * @var array
     */
    private $settings = [];

    /**
     * @param array $settings
     */
    public function __construct(array $settings = array())
    {
        foreach ($settings as $values) {
            $this->settings[] = (object) $values;
        }
    }

    /**
     * @return array
     */
    public function getSpEntityIdsWithoutConsent()
    {
        return array_filter(
            array_map(
                function ($settings) {
                    if ($settings->type === self::CONSENT_DISABLED) {
                        return $settings->name;
                    }
                },
                $this->settings
            )
        );
    }

    /**
     * @param string $entityId
     */
    public function isEnabled($entityId)
    {
        $settings = $this->findSettingsFor($entityId);
        if ($settings !== null) {
            return $settings->type !== self::CONSENT_DISABLED;
        }

        return true;
    }

    /**
     * @param string $entityId
     */
    public function isMinimal($entityId)
    {
        $settings = $this->findSettingsFor($entityId);
        if ($settings !== null) {
            return $settings->type === self::CONSENT_MINIMAL;
        }

        return false;
    }

    /**
     * @param string $entityId
     */
    private function findSettingsFor($entityId)
    {
        foreach ($this->settings as $values) {
            if ($values->name !== $entityId) {
                continue;
            }

            return $values;
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->settings;
    }
}
