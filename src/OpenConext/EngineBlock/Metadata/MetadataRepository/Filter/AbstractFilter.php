<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Filter;

/**
 * Class AbstractFilter
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Filter
 */
abstract class AbstractFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return get_class($this);
    }
}
