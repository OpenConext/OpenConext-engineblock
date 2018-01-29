<?php

namespace OpenConext\EngineBlock\Metadata;

/**
 * A SAML2 metadata logo.
 * @package OpenConext\EngineBlock\Metadata
 */
class Logo
{
    public $height = null;
    public $width = null;
    public $url = null;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
    }
}
