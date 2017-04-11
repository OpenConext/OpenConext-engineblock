<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

class ApiContext extends AbstractSubContext
{
    /**
     * @var string
     */
    private $apiBaseUrl;

    public function __construct($apiBaseUrlWithoutScheme)
    {
        $this->apiBaseUrl = 'https://' . $apiBaseUrlWithoutScheme;
    }
}
