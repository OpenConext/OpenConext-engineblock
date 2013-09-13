<?php

$rootDir = realpath(__DIR__ . '/../../');

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;

// Require 3rd-party libraries here:

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    private $hostUrl;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Set host url for functional testing
        $engineblockApp = EngineBlock_ApplicationSingleton::getInstance();
        $engineblockApp->bootstrap();
        $engineBlockLocation = $engineblockApp->getConfiguration()->get('functionalTesting')->get('engineBlockUrl');
        $parsedUrl = parse_url($engineBlockLocation);
        $this->hostUrl = 'https://' . $parsedUrl['host'];
    }

    /**
     * @Given /^Dummy Idp is configured to use the "([^"]*)" testcase$/
     */
    public function dummyIdpIsConfiguredToUseTheTestcase($testCase)
    {
        $this->getSession()->visit($this->hostUrl . '/dummy/idp?testCase=' . urlencode($testCase));
    }

    /**
     * @Given /^Dummy Sp is configured to use the "([^"]*)" testcase$/
     */
    public function dummySpIsConfiguredToUseTheTestcase($testCase)
    {
        $this->getSession()->visit($this->hostUrl . '/dummy/sp?testCase=' . urlencode($testCase));
    }

    /**
     * @When /^I go to engine-test "([^"]*)"$/
     */
    public function iGoToEngineTest($uri)
    {
        $this->getSession()->visit($this->hostUrl . $uri);
    }
}