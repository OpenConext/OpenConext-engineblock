<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use SAML2\Constants;
use SAML2\XML\saml\NameID;

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
            $orderA = 999999;
            $orderB = 999999;
            if (isset($definitions[$a]['DisplayOrder'])) {
                $orderA = $definitions[$a]['DisplayOrder'];
            }
            if (isset($definitions[$b]['DisplayOrder'])) {
                $orderB = $definitions[$b]['DisplayOrder'];
            }
            // fix to get same results on php7 and < php7
            if ($orderA === $orderB) {
                return ($a < $b ? 1 : -1);
            }
            return $orderA - $orderB;
        });
        return $attributes;
    }

    public function normalizeEptiAttributeValue(array $attributes)
    {
        foreach ($attributes as $attributeName => $attributeValues) {
            if ($attributeName !== Constants::EPTI_URN_MACE && $attributeName !== Constants::EPTI_URN_OID) {
                continue;
            }

            $attributes[$attributeName] = array_map(function (NameID $nameId) {
                return $nameId->getValue();
            }, $attributeValues);
        }

        return $attributes;
    }

    /**
     * @return RequestedAttribute[]
     */
    public function getRequestedAttributes()
    {
        $attributes = [];
        foreach ($this->findRequestedAttributeIds() as $attributeId) {
            $attributes[] = new RequestedAttribute($attributeId);
        }

        foreach ($this->findRequiredAttributeIds() as $attributeId) {
            $attributes[] = new RequestedAttribute($attributeId, true);
        }

        return $attributes;
    }

    /**
     * @return string[]
     */
    private function findRequestedAttributeIds()
    {
        return $this->findAttributeIdsWithMinConditionForType('warning');
    }

    /**
     * @return string[]
     */
    private function findRequiredAttributeIds()
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
