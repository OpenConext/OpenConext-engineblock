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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use EngineBlock_Attributes_Metadata;
use OpenConext\Value\Saml\NameIdFormat;
use SAML2\XML\saml\NameID;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFunction;
use Twig_Extension;

/**
 * Used to perform certain view related operations on metadata. For example this extension provides a function that
 * can sort metadata by display order.
 */
class Metadata extends Twig_Extension
{
    /**
     * @var string
     */
    private $attributeMetadata;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(EngineBlock_Attributes_Metadata $attributesMetadata, TranslatorInterface $translator)
    {
        $this->attributeMetadata = $attributesMetadata;
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'sortByDisplayOrder',
                [$this, 'sortByDisplayOrder'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'attributeSourceLogoUrl',
                [$this, 'getAttributeSourceLogoUrl']
            ),
            new TwigFunction(
                'attributeSourceDisplayName',
                [$this, 'getAttributeSourceDisplayName'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'attributeShortName',
                [$this, 'getAttributeShortName'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'attributeName',
                [$this, 'getAttributeName'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Sort, group and normalize attributes for display on the consent page.
     *
     * To preserve backwards compatibility in the consent template, the attributes are
     * not grouped by attribute source when $attributeSources is omitted.
     *
     * @param $attributes
     * @param array|null $attributeSources
     * @param NaneID|null $nameId
     * @return array
     */
    public function sortByDisplayOrder($attributes, array $attributeSources = null, NameID $nameId = null)
    {
        $sortedAttributes = $this->attributeMetadata->sortByDisplayOrder($attributes);
        $normalizedAttributes = $this->attributeMetadata->normalizeEptiAttributeValue($sortedAttributes);

        if ($attributeSources === null) {
            return $normalizedAttributes;
        }

        return $this->groupAttributesBySource($normalizedAttributes, $attributeSources, $nameId);
    }

    /**
     * Get logo for attribute source.
     *
     * @param string $source Source identifier (e.g. "voot")
     * @return string URL
     */
    public function getAttributeSourceLogoUrl($source)
    {
        return $this->translator->trans('consent_attribute_source_logo_url_' . strtolower($source));
    }

    /**
     * Get user-friendly attribute source name.
     *
     * @param string $source Source identifier (e.g. "voot")
     * @return string
     */
    public function getAttributeSourceDisplayName($source)
    {
        return $this->translator->trans('consent_attribute_source_' . strtolower($source));
    }

    /**
     * Looks up the Attribute Id in the attribute metadata definition. If it is found, the name defined in the
     * definition list is used. Otherwise, falls back on the attribute id that was passed in the first place.
     *
     * @param $attributeId
     * @param string $preferecLocale
     * @return mixed
     */
    public function getAttributeShortName($attributeId, $preferecLocale = 'en')
    {
        $attributeShortName = $this->getAttributeName($attributeId, $preferecLocale);
        if (trim($attributeShortName) === '') {
            $attributeShortName = $attributeId;
        }

        return $attributeShortName;
    }

    /**
     * @param $attributeId
     * @param string $preferedLocale
     * @return string
     */
    public function getAttributeName($attributeId, $preferedLocale = 'en')
    {
        return $this->attributeMetadata->getName($attributeId, $preferedLocale);
    }

    private function groupAttributesBySource($attributes, array $attributeSources = array(), NameID $nameID = null)
    {
        $groupedAttributes = array(
            'idp' => array(),
        );

        if ($nameID && ($nameID->getFormat() == NameIdFormat::PERSISTENT_IDENTIFIER || $nameID->getFormat() == NameIdFormat::UNSPECIFIED)) {
            $groupedAttributes['engineblock']['name_id'] = $nameID->getValue();
        }

        foreach ($attributes as $attributeName => $attributeValue) {
            if (isset($attributeSources[$attributeName])) {
                $sourceName = $attributeSources[$attributeName];
            } else {
                $sourceName = 'idp';
            }

            $groupedAttributes[$sourceName][$attributeName] = $attributeValue;
        }

        return $groupedAttributes;
    }
}
