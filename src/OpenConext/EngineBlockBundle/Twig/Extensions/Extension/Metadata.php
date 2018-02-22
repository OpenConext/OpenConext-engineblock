<?php

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use EngineBlock_Attributes_Metadata;
use Twig\TwigFunction;
use Twig_Extension;
use Zend_Translate_Adapter;

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
     * @var Zend_Translate_Adapter
     */
    private $translator;

    public function __construct(EngineBlock_Attributes_Metadata $attributesMetadata, Zend_Translate_Adapter $translator)
    {
        $this->attributeMetadata = $attributesMetadata;
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('sortByDisplayOrder', [$this, 'sortByDisplayOrder'], ['is_safe' => ['html']]),
            new TwigFunction(
                'attributeSourceLogoUrl', [$this, 'getAttributeSourceLogoUrl'], ['is_safe' => ['html']]
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
     * @return array
     */
    public function sortByDisplayOrder($attributes, array $attributeSources = null)
    {
        $sortedAttributes = $this->attributeMetadata->sortByDisplayOrder($attributes);
        $normalizedAttributes = $this->attributeMetadata->normalizeEptiAttributeValue($sortedAttributes);

        if ($attributeSources === null) {
            return $normalizedAttributes;
        }

        return $this->groupAttributesBySource($normalizedAttributes, $attributeSources);
    }

    /**
     * Get logo for attribute source.
     *
     * @param string $source Source identifier (e.g. "voot")
     * @return string URL
     */
    public function getAttributeSourceLogoUrl($source)
    {
        return $this->translator->translate('consent_attribute_source_logo_url_' . strtolower($source));
    }

    /**
     * Get user-friendly attribute source name.
     *
     * @param string $source Source identifier (e.g. "voot")
     * @return string
     */
    public function getAttributeSourceDisplayName($source)
    {
        return $this->translator->translate('consent_attribute_source_' . strtolower($source));
    }

    /**
     * Looks up the Attribute Id in the attribute metadata definition. If it is found, the name defined in the
     * definition list is used. Otherwise, falls back on the attribute id that was passed in the first place.
     *
     * @param $attributeId
     * @param string $ietfLanguageTag
     * @return mixed
     */
    public function getAttributeShortName($attributeId, $ietfLanguageTag = 'en')
    {
        $attributeShortName = $this->attributeMetadata->getName($attributeId, $ietfLanguageTag);
        if (trim($attributeShortName) === '') {
            $attributeShortName = $attributeId;
        }

        return $attributeShortName;
    }


    private function groupAttributesBySource($attributes, array $attributeSources = array())
    {
        $groupedAttributes = array(
            'idp' => array(),
        );

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
