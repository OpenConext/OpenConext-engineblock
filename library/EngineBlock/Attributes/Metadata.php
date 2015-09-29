<?php

/**
 * Metadata for (known) attributes.
 */
class EngineBlock_Attributes_Metadata
{
    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $definitions;

    /**
     * @param $definitions
     * @param Psr\Log\LoggerInterface $logger
     */
    public function __construct(array $definitions, Psr\Log\LoggerInterface $logger)
    {
        $this->definitions = $definitions;
        $this->logger = $logger;
    }

    /**
     * Get the (short) name for the attribute.
     *
     * @param string $attributeId
     * @param string $ietfLanguageTag
     * @return string
     */
    public function getName($attributeId, $ietfLanguageTag = 'en')
    {
        return $this->getTypeForLang($attributeId, 'Name', $ietfLanguageTag);
    }

    /**
     * Get the name if possible, fall back to the attribute ID if not.
     *
     * @param string $attributeId
     * @param string $ietfLanguageTag
     * @return string
     */
    public function getNameWithFallback($attributeId, $ietfLanguageTag = 'en')
    {
        $name = $this->getName($attributeId, $ietfLanguageTag);

        if (empty($name)) {
            $name = $attributeId;
        }

        return $name;
    }

    /**
     * Get the name of the attribute for purposes of showing to the end-user in the consent screen.
     *
     * @param string $attributeId
     * @param string $ietfLanguageTag
     * @return string
     */
    public function getNameForConsent($attributeId, $ietfLanguageTag = 'en')
    {
        $name = $this->getName($attributeId, $ietfLanguageTag);

        if ($this->definitions[$attributeId]['DisplayConsent']) {
            return $name;
        }

        return '';
    }

    /**
     * @param string $attributeId
     * @param string $ietfLanguageTag
     * @return string
     */
    public function getDescription($attributeId, $ietfLanguageTag = 'en')
    {
        return $this->getTypeForLang($attributeId, 'Description', $ietfLanguageTag);
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function sortByDisplayOrder(array $attributes)
    {
        $definitions = $this->definitions;
        uksort($attributes, function ($a, $b) use ($definitions) {
            $orderA = -1;
            $orderB = -1;
            if (isset($definitions[$a]['DisplayOrder'])) {
                $orderA = $definitions[$a]['DisplayOrder'];
            }
            if (isset($definitions[$b]['DisplayOrder'])) {
                $orderB = $definitions[$b]['DisplayOrder'];
            }
            return $orderA - $orderB;
        });
        return $attributes;
    }

    /**
     * @return string[]
     */
    public function findRequestedAttributeIds()
    {
        return $this->findAttributeIdsWithMinConditionForType('warning');
    }

    /**
     * @return string[]
     */
    public function findRequiredAttributeIds()
    {
        return $this->findAttributeIdsWithMinConditionForType('error');
    }

    /**
     * Find all attribute ids with a 'min' (or required) condition of a given type ('warning' / 'error').
     *
     * @param string $type
     * @return array
     */
    private function findAttributeIdsWithMinConditionForType($type)
    {
        $attributeIds = array();
        foreach ($this->definitions as $attributeId => $attributeDefinition) {
            if (isset($attributeDefinition['__original__'])) {
                continue;
            }

            if (!isset($attributeDefinition['Conditions'][$type]['min'])) {
                continue;
            }

            if ((int) $attributeDefinition['Conditions'][$type]['min'] < 1) {
                continue;
            }

            $attributeIds[] = $attributeId;
        }
        return $attributeIds;
    }

    /**
     * @param string $id
     * @param string $type
     * @param string $ietfLanguageTag
     * @return string
     */
    private function getTypeForLang($id, $type, $ietfLanguageTag = 'en')
    {
        if (isset($this->definitions[$id][$type][$ietfLanguageTag])) {
            return $this->definitions[$id][$type][$ietfLanguageTag];
        }
        $this->logger->notice("Attribute lookup failure '$id' has no '$type' for language '$ietfLanguageTag'");
        return '';
    }
}
