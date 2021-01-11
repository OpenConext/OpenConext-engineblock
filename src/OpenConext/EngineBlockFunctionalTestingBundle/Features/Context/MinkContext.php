<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext as BaseMinkContext;
use DOMDocument;
use DOMXPath;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RuntimeException;
use SAML2\XML\mdui\Common;
use SAML2\XML\shibmd\Scope;

/**
 * Mink-enabled context.
 */
class MinkContext extends BaseMinkContext
{
    /**
     * @var array a list of window names identified by the name the tester refers to them in the step definitions.
     * @example ['My tab' => 'WindowNameGivenByBrowser', 'My other tab' => 'WindowNameGivenByBrowser']
     */
    private $windows = [];

    /**
     * @Given /^Xdebug step debugging is enabled in the browser$/
     */
    public function putDebugCookie()
    {
        $driver = $this->getSession()->getDriver();
        $driver->setCookie('XDEBUG_SESSION', 'PHPSTORM');
    }

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
        $xpathObj->registerNamespace('ds', XMLSecurityDSig::XMLDSIGNS);
        $xpathObj->registerNamespace('mdui', Common::NS);
        $xpathObj->registerNamespace('shibmd', Scope::NS);
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
        $xpathObj->registerNamespace('ds', XMLSecurityDSig::XMLDSIGNS);
        $xpathObj->registerNamespace('mdui', Common::NS);
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

    /**
     * @Given /^I should see URL "([^"]*)"$/
     */
    public function iShouldSeeUrl($url)
    {
        $this->assertSession()->responseContains($url);
    }

    /**
     * @Given /^I should not see URL "([^"]*)"$/
     */
    public function iShouldNotSeeUrl($url)
    {
        $this->assertSession()->responseNotContains($url);
    }

    /**
     * @Given /^I open (\d+) browser tabs identified by "([^"]*)"$/
     */
    public function iOpenTwoBrowserTabsIdentifiedBy($numberOfTabs, $tabNames)
    {
        $tabs = explode(',', $tabNames);
        if (count($tabs) != $numberOfTabs) {
            throw new RuntimeException(
                'Please identify all tabs you are opening in order to refer to them at a later stage'
            );
        }

        foreach ($tabs as $tab) {
            $this->getMink()
                ->getSession()
                ->executeScript("window.open('/','_blank');");

            $windowsNames = $this->getSession()->getWindowNames();

            if (!$windowsNames) {
                throw new RuntimeException('The windows where not opened correctly.');
            }
            // Grab the window name (which is the last one added to the window list)
            $windowName = array_pop($windowsNames);
            // Keep track of the opened windows in order allow switching between them
            $this->windows[trim($tab)] = $windowName;
        }
    }

    /**
     * @Given /^I switch to "([^"]*)"$/
     */
    public function iSwitchToWindow($windowName)
    {
        $this->switchToWindow($windowName);
    }

    public function switchToWindow($windowName)
    {
        if (!isset($this->windows[$windowName])) {
            throw new RuntimeException(sprintf('Unknown window/tab name "%s"', $windowName));
        }
        $this->getSession()->switchToWindow($this->windows[$windowName]);
    }

    /**
     * @Then /^I should see (\d+) links on the front page$/
     */
    public function iShouldSeeLinksOnTheFrontPage($expectedNumberOfLinks)
    {
        $anchors = $this->getSession()->getPage()->findAll('css', '#engine-main-page a');
        if (count($anchors) != $expectedNumberOfLinks) {
            throw new ExpectationException(
                sprintf(
                    'The expected amount (%d) of metadata links could not be found on the page',
                    $expectedNumberOfLinks
                ),
                $this->getSession()
            );
        }
    }
}
