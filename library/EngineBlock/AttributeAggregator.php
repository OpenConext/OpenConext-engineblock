<?php

/**
 * Aggregate attributes from multiple sources.
 *
 * @todo Should be done asynchronously, see also:
 * https://wiki.surfnetlabs.nl/confluence/display/coindo2010/Asynchronous+attribute+aggregation+from+Attribute+Providers
 */
class EngineBlock_AttributeAggregator 
{
    protected $_providers = array();

    public function __construct(array $providers)
    {
        $this->_providers = $providers;
    }

    public function getAttributes($uid)
    {
        $attributes = array();
        foreach ($this->_providers as $provider) {
            $attributes = array_merge_recursive($attributes, $provider->getAttributes($uid));
        }
        return $attributes;
    }
}
