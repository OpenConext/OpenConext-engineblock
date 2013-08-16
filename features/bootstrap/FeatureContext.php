<?php

$rootDir = realpath(__DIR__ . '/../../');
require_once $rootDir . '/library/simplesamlphp/lib/_autoload.php';

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
    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
    }

    /**
     * @Given /^Dummy Idp is configured to give the "([^"]*)" response$/
     */
    public function dummyIdpIsConfiguredToGiveTheResponse($responseType)
    {
        $this->getSession()->visit('https://engine-test.demo.openconext.org/dummy-idp?responseType=' . urlencode($responseType));
    }
}