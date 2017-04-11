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

    /**
     * @When /^I check EngineBlock's heartbeat$/
     */
    public function iCheckEngineBlockSHeartbeat()
    {
        $this->visit('/');
    }

    private function visit($path)
    {
        $this->getMainContext()->getMinkContext()->visit($this->apiBaseUrl . $path);
    }
}
