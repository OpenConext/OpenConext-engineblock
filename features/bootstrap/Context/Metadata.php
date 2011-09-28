<?php

namespace EngineBlock\Behat\Context;

require_once 'mink/autoload.php';
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use \Behat\Behat\Context\BehatContext;
use \Behat\Behat\Exception\PendingException;

class Metadata extends BehatContext
{
    /**
     * @When /^I go to the metadata url of Engineblock$/
     */
    public function iGoToTheMetadataUrlOfEngineblock()
    {

        $url = "https://engine.test.surfconext.nl/authentication/proxy/idps-metadata";
        $this->storeXMLelement($url);
    }

    /**
     * @Then /^I should retrieve all metadata for all configured IDPs including the allow-none IDP "([^"]*)"$/
     */
    public function iShouldRetrieveAllMetadataForAllConfiguredIdpsIncludingTheAllowNoneIdp($allownNoneIDP)
    {
        $entities = $this->getMainContext()->xml->xpath("//md:EntityDescriptor[@entityID='$allownNoneIDP']");
        $count = count($entities);
        assertEquals(1, $count);
    }

    /**
     * @When /^I go to the metadata url of Engineblock with the sp-entity-id attribute with value "([^"]*)"$/
     */
    public function iGoToTheMetadataUrlOfEngineblockWithTheSpEntityIdAttributeWithValue($spEntityId)
    {
        $url = "https://engine.test.surfconext.nl/authentication/proxy/idps-metadata?sp-entity-id=" .
               urlencode($spEntityId);
        $this->storeXMLelement($url);
    }

    /**
     * @Then /^I should not retrieve the metadata for IDP "([^"]*)"$/
     */
    public function iShouldNotRetrieveTheMetadataForIdp($allownNoneIDP)
    {
        $entities = $this->getMainContext()->xml->xpath("//md:EntityDescriptor[@entityID='$allownNoneIDP']");
        $count = count($entities);
        assertEquals(0, $count);
    }

    /**
     * @Given /^I should retrieve the metadata for the SP "([^"]*)"$/
     */
    public function iShouldRetrieveTheMetadataForTheSp($spEntityId)
    {
        $entities = $this->getMainContext()->xml->xpath("//md:EntityDescriptor[@entityID='$spEntityId']");
        $count = count($entities);
        assertEquals(1, $count);
    }

    protected function storeXMLelement($url)
    {
        //apparently behat expects html, not xml, so we use a 'hack'
        $metadata = file_get_contents($url);
        $xml = simplexml_load_string($metadata);
        $xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $this->getMainContext()->xml = $xml;
    }


}