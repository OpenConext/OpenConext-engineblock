<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext as BaseMinkContext;
use DOMDocument;
use DOMXPath;

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
        $document = new DOMDocument();
        $document->loadXML($this->getSession()->getPage()->getContent());

        $xpathObj = new DOMXPath($document);
        $nodeList = $xpathObj->query($xpath);

        if (!$nodeList || $nodeList->length === 0) {
            $message = sprintf('The xpath "%s" did not result in at least one match.', $xpath);
            throw new ExpectationException($message, $this->getSession());
        }
    }

    /**
     * @Then /^the response should not match xpath \'([^\']*)\'$/
     */
    public function theResponseShouldNotMatchXpath($xpath)
    {
        $document = new DOMDocument();
        $document->loadXML($this->getSession()->getPage()->getContent());

        $xpathObj = new DOMXPath($document);
        $nodeList = $xpathObj->query($xpath);

        if ($nodeList && $nodeList->length > 0) {
            $message = sprintf(
                'The xpath "%s" resulted in "%d" matches, where it should result in no matches"',
                $xpath,
                $nodeList->length
            );
            throw new ExpectationException($message, $this->getSession());
        }
    }
}
