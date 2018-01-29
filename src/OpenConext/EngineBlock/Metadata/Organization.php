<?php

namespace OpenConext\EngineBlock\Metadata;

/**
 * Class Organization
 * @package OpenConext\EngineBlock\Metadata
 */
class Organization
{
    public $name;
    public $displayName;
    public $url;

    /**
     * @param $name
     * @param $displayName
     * @param $url
     */
    public function __construct($name, $displayName, $url)
    {
        $this->displayName = $displayName;
        $this->name = $name;
        $this->url = $url;
    }
}
