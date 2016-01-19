<?php

namespace OpenConext\EngineBlock\FunctionalTestingBundle\Features\Context;

use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext as BaseMinkContext;

/**
 * Mink-enabled context.
 */
class MinkContext extends BaseMinkContext
{
    /**
     * @Then /^the response should contain \'([^\']*)\'$/
     */
    public function theResponseShouldContain($string)
    {
        $this->assertSession()->responseContains($string);
    }

    /**
     * @Then /^the response should match xpath \'([^\']*)\'$/
     */
    public function theResponseShouldMatchXpath($xpath)
    {
        $document = new \DOMDocument();
        $document->loadXML($this->getSession()->getPage()->getContent());

        $xpathObj = new \DOMXPath($document);
        $nodeList = $xpathObj->query($xpath);

        if (!$nodeList || !$nodeList->length === 0) {
            $message = sprintf('The xpath "%s" was not found anywhere in the response of the current page.', $xpath);
            throw new ExpectationException($message, $this->getSession());
        }
    }
}
