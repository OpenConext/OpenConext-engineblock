<?php

namespace OpenConext\EngineBlock\Metadata;

/**
 * An indexed service is a Service definition with an explicit ordering in the form of an index.
 * @package OpenConext\EngineBlock\Metadata
 */
class IndexedService extends Service
{
    /**
     * @var int
     */
    public $serviceIndex;

    /**
     * Note that null and false are NOT the same in this context.
     *
     * @var bool|null
     */
    public $isDefault = null;

    /**
     * @param string $location
     * @param string $binding
     * @param $serviceIndex
     * @param bool|null $isDefault
     */
    public function __construct($location, $binding, $serviceIndex, $isDefault = null)
    {
        $this->isDefault    = $isDefault;
        $this->serviceIndex = $serviceIndex;

        parent::__construct($location, $binding);
    }
}
