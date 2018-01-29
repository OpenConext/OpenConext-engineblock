<?php

namespace OpenConext\EngineBlock\Metadata;

/**
 * Class Service
 * @package OpenConext\EngineBlock\Metadata
 */
class Service
{
    /**
     * @var string
     */
    public $binding;

    /**
     * @var string
     */
    public $location;

    /**
     * @param string $location
     * @param string $binding
     */
    public function __construct($location, $binding)
    {
        $this->binding  = $binding;
        $this->location = $location;
    }
}
