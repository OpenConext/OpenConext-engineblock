<?php

namespace OpenConext\EngineBlock\ProfileBundle\Service;

class AttributeFilter
{
    static $filteredAttributeIds = array(
        'urn:oid:2.5.4.42',
        'urn:oid:2.5.4.3',
        'urn:oid:2.5.4.4',
        'urn:oid:2.16.840.1.113730.3.1.241',
        'urn:oid:0.9.2342.19200300.100.1.1',
        'urn:oid:0.9.2342.19200300.100.1.3',
        'urn:oid:1.3.6.1.4.1.1466.115.121.1.15',
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.6',
        'coin:',
        'urn:nl.surfconext.licenseInfo',
        'urn:mace:dir:attribute-def:isMemberOf',
        'urn:oid:1.3.6.1.4.1.1076.20.40.40.1',
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.10'
    );

    public function filter(array $attributes)
    {
        // php 5.3 workaround
        $partialIdsToFilter = self::$filteredAttributeIds;
        return array_filter($attributes, function ($attributeId) use ($partialIdsToFilter) {
            foreach ($partialIdsToFilter as $filteringId) {
                if (strpos($attributeId, $filteringId) !== false) {
                    return false;
                }
            }

            return true;
        }, ARRAY_FILTER_USE_KEY);
    }
}
