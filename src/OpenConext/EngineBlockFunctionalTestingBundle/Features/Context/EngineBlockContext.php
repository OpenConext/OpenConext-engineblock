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

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use DOMDocument;
use DOMXPath;
use Ingenerator\BehatTableAssert\TableParser\HTMLTable;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingAttributeAggregationClient;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingAuthenticationLoopGuard;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingFeatureConfiguration;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingPdpClient;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Service\EngineBlock;
use RuntimeException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Both set up and tasks can be a lot...
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) Both set up and tasks can be a lot...
 * @SuppressWarnings(PHPMD.TooManyMethods) Both set up and tasks can be a lot...
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Due to all integration specific features
 * @SuppressWarnings(PHPMD.ExcessivePublicCount) Both set up and tasks can be a lot...
 */
class EngineBlockContext extends AbstractSubContext
{
    /**
     * @var ServiceRegistryFixture
     */
    private $serviceRegistryFixture;

    /**
     * @var EngineBlock
     */
    private $engineBlock;

    /**
     * @var EntityRegistry
     */
    private $mockSpRegistry;

    /**
     * @var EntityRegistry
     */
    private $mockIdpRegistry;

    /**
     * @var FunctionalTestingFeatureConfiguration
     */
    private $features;

    /**
     * @var FunctionalTestingAuthenticationLoopGuard
     */
    private $authenticationLoopGuard;

    /**
     * @var boolean
     */
    private $usingFeatures = false;

    /**
     * @var FunctionalTestingPdpClient
     */
    private $pdpClient;

    /**
     * @var boolean
     */
    private $usingPdp = false;

    /*
     * @var boolean
     */
    private $usingAuthenticationLoopGuard = false;

    /**
     * @var string
     */
    private $engineBlockDomain;

    /**
     * @var FunctionalTestingAttributeAggregationClient
     */
    private $attributeAggregationClient;

    /**
     * @var boolean
     */
    private $usingAttributeAggregationClient = false;

    /**
     * @var string
     */
    private $currentRequestId = '';

    /**
     * @param ServiceRegistryFixture $serviceRegistry
     * @param EngineBlock $engineBlock
     * @param EntityRegistry $mockSpRegistry
     * @param EntityRegistry $mockIdpRegistry
     * @param FunctionalTestingFeatureConfiguration $features
     * @param FunctionalTestingPdpClient $pdpClient
     * @param FunctionalTestingAuthenticationLoopGuard $authenticationLoopGuard
     * @param FunctionalTestingAttributeAggregationClient $attributeAggregationClient
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ServiceRegistryFixture $serviceRegistry,
        EngineBlock $engineBlock,
        EntityRegistry $mockSpRegistry,
        EntityRegistry $mockIdpRegistry,
        FunctionalTestingFeatureConfiguration $features,
        FunctionalTestingPdpClient $pdpClient,
        FunctionalTestingAuthenticationLoopGuard $authenticationLoopGuard,
        FunctionalTestingAttributeAggregationClient $attributeAggregationClient
    ) {
        $this->serviceRegistryFixture = $serviceRegistry;
        $this->engineBlock = $engineBlock;
        $this->mockSpRegistry = $mockSpRegistry;
        $this->mockIdpRegistry = $mockIdpRegistry;
        $this->features = $features;
        $this->pdpClient = $pdpClient;
        $this->authenticationLoopGuard = $authenticationLoopGuard;
        $this->attributeAggregationClient = $attributeAggregationClient;
    }

    /**
     * @Given /^an EngineBlock instance on "([^"]*)"$/
     */
    public function anEngineBlockInstanceOn($domain)
    {
        // Add all known IdPs
        $this->serviceRegistryFixture
            ->reset()
            ->registerSp(
                "OpenConext EngineBlock",
                "https://engine.$domain/authentication/sp/metadata",
                "https://engine.$domain/authentication/sp/consume-assertion"
            )
            ->registerIdp(
                "OpenConext EngineBlock",
                "https://engine.$domain/authentication/idp/metadata",
                "https://engine.$domain/authentication/idp/single-sign-on"
            )
            ->save();

        $this->engineBlockDomain = 'https://engine.' . $domain;
    }

    /**
     * @Given /^I follow the EB debug screen to the IdP$/
     */
    public function iFollowTheEbDebugScreenToTheIdp()
    {
        // Support for HTTP-Post
        $hasSubmitButton = $this->getMinkContext()->getSession()->getPage()->findButton('Submit');
        if ($hasSubmitButton) {
            $this->getMinkContext()->pressButton('submitbutton');
            return;
        }

        // Default to HTTP-Redirect
        $this->getMinkContext()->clickLink('GO');
    }

    /**
     * @Given /^I pass through EngineBlock$/
     */
    public function iPassThroughEngineblock()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit');
    }

    /**
     * @Given /^EngineBlock raises an unexpected error$/
     */
    public function engineBlockRaisesARuntimeException()
    {
        $mink = $this->getMinkContext();
        // By setting the throwException cookie, the test stand in of the SsoRequestValidator will throw an exception
        $mink->getSession()->setCookie('throwException', 'EngineBlock in functional testing mode threw a RuntimeException');
    }

    /**
     * @Then /^I should see the following "([^"]*)" attributes listed on the consent page:$/
     */
    public function iSeeTheAttributesFromSourceOnConsentPage($source, TableNode $attributes)
    {
        $mink = $this->getMinkContext();
        $tableSelector = 'tbody[data-attr-source="' . strtolower($source) . '"]';
        $tableTemplate = <<<HTML
<table>
    <thead>
        <tr>
            <td>Name</td>
            <td>Value</td>
        </tr>
    </thead>
    <tbody>%s</tbody>
</table>
HTML;

        $actualTable = HTMLTable::fromHTMLString(
            sprintf(
                $tableTemplate,
                $mink->assertSession()->elementExists('css', $tableSelector)->getHtml()
            )
        );

        $rows = $actualTable->getRows();

        // Remove the first row (IDP name, is this correct?)
        unset($rows[1]);

        // Remove the last row (Show more and separator)
        array_pop($rows);
        array_pop($rows);

        $actualTable = new TableNode($rows);

        $assert = new \Ingenerator\BehatTableAssert\AssertTable;
        $assert->isComparable($attributes, $actualTable, []);
    }

    /**
     * @Given /^I give my consent$/
     */
    public function iGiveMyConsent()
    {
        $mink = $this->getMinkContext();

        if (strstr($mink->getSession()->getPage()->getHtml(), 'accept_terms_button')) {
            $mink->pressButton('accept_terms_button');
        }
    }

    /**
     * @Given /^An IdP initiated Single Sign on for SP "([^"]*)" is triggered by IdP "([^"]*)"$/
     */
    public function anIdpInitiatedSingleSignOnForSpIsTriggeredByIdP($spName, $idpName)
    {
        $mockSp = $this->mockSpRegistry->get($spName);
        $mockIdP = $this->mockIdpRegistry->get($idpName);

        $mink = $this->getMinkContext();
        $mink->visit(
            $this->engineBlock->unsolicitedLocation($mockIdP->entityId(), $mockSp->entityId())
        );
    }

    /**
     * @Given /^An IdP initiated Single Sign on for SP "([^"]*)" is triggered by IdP "([^"]*)" and specifies a valid signing key$/
     */
    public function anIdpInitiatedSingleSignOnForSpIsTriggeredByIdPWithSigningKey($spName, $idpName)
    {
        $mockSp = $this->mockSpRegistry->get($spName);
        $mockIdP = $this->mockIdpRegistry->get($idpName);

        $mink = $this->getMinkContext();
        $mink->visit(
            $this->engineBlock->unsolicitedLocation($mockIdP->entityId(), $mockSp->entityId(), 'default')
        );
    }

    /**
     * @Given /^An IdP initiated Single Sign on for SP "([^"]*)" is triggered by IdP "([^"]*)" and specifies an invalid signing key$/
     */
    public function anIdpInitiatedSingleSignOnForSpIsTriggeredByIdPWithInvalidSigningKey($spName, $idpName)
    {
        $mockSp = $this->mockSpRegistry->get($spName);
        $mockIdP = $this->mockIdpRegistry->get($idpName);

        $mink = $this->getMinkContext();
        $mink->visit(
            $this->engineBlock->unsolicitedLocation($mockIdP->entityId(), $mockSp->entityId(), 'does-not-exist')
        );
    }

    /**
     * @Given /^An IdP initiated Single Sign on for SP "([^"]*)" is incorrectly triggered by IdP "([^"]*)"$/
     */
    public function anIdpInitiatedSingleSignOnForSpIsIncorrectlyTriggeredByIdP($spName, $idpName)
    {
        $mockSp = $this->mockSpRegistry->get($spName);
        $mockIdP = $this->mockIdpRegistry->get($idpName);

        $mink = $this->getMinkContext();
        $mink->visit(
            $this->engineBlock->unsolicitedLocation($mockIdP->entityId() . 'I made a booboo', $mockSp->entityId())
        );
    }

    /**
     * @Given /^An IdP initiated Single Sign on for SP "([^"]*)" with invalid parameter, by IdP "([^"]*)"$/
     */
    public function anIdpInitiatedSingleSignOnForSpIsInvalidParameterByIdP($spName, $idpName)
    {
        $mockSp = $this->mockSpRegistry->get($spName);
        $mockIdP = $this->mockIdpRegistry->get($idpName);

        $mink = $this->getMinkContext();
        $mink->visit(
            $this->engineBlock->unsolicitedLocationInvalidParam($mockIdP->entityId(), $mockSp->entityId())
        );
    }

    /**
     * @Given /^I select "([^"]*)" on the WAYF$/
     */
    public function iSelectOnTheWAYF($idpName)
    {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);

        if (!$mockIdp) {
            throw new RuntimeException(
                sprintf('Unable to find idp with name "%s"', $idpName)
            );
        }

        $selector = 'input[type="submit"][data-entityid="' . $mockIdp->entityId() . '"]';

        $mink = $this->getMinkContext()->getSession()->getPage();
        $button = $mink->find('css', $selector);

        if (!$button) {
            throw new RuntimeException(sprintf('Unable to find button with selector "%s"', $selector));
        }

        $button->click();
    }

    /**
     * @Then /^The process form should have the "([^"]*)" field$/
     */
    public function iSeeACertainFormFieldOnTheProcessForm($formFieldName)
    {
        $selector = 'input[name="' . $formFieldName . '"]';
        $mink = $this->getMinkContext()->getSession()->getPage();
        $formField = $mink->find('css', $selector);

        if (!$formField) {
            throw new RuntimeException(sprintf('The "%s" form field should have been on the form.', $formFieldName));
        }
    }

    /**
     * @Then /^The process form should not have the "([^"]*)" field$/
     */
    public function iDoNotSeeACertainFormFieldOnTheProcessForm($formFieldName)
    {
        $selector = 'input[name="' . $formFieldName . '"]';
        $mink = $this->getMinkContext()->getSession()->getPage();
        $formField = $mink->find('css', $selector);

        if (!is_null($formField)) {
            throw new RuntimeException(sprintf('The "%s" form field should not have been on the form.', $formFieldName));
        }
    }

    /**
     * @Then /^I should see the "Request access" button$/
     */
    public function iSeeTheRequestAccessButton()
    {
        $selector = 'a.noaccess';

        $mink = $this->getMinkContext()->getSession()->getPage();
        $button = $mink->find('css', $selector);

        if (!$button) {
            throw new RuntimeException(sprintf('Unable to find Request access button "%s"', $selector));
        }
    }

    /**
     * @Then /^I should not see the "Request access" button$/
     */
    public function iDoNotSeeTheRequestAccessButton()
    {
        try {
            $this->iSeeTheRequestAccessButton();
        } catch (RuntimeException $e) {
            return;
        }

        throw new RuntimeException('Request access button found on page');
    }

    /**
     * @Given /^I log out at EngineBlock$/
     */
    public function iLogoutAtEngineBlock()
    {
        $this->getMinkContext()->visit($this->engineBlock->logoutLocation());
    }

    /**
     * @Given /^feature "([^"]*)" is enabled$/
     */
    public function featureIsEnabled($feature)
    {
        $this->usingFeatures = true;
        $this->features->save($feature, true);
    }

    /**
     * @Given /^feature "([^"]*)" is disabled$/
     */
    public function featureIsDisabled($feature)
    {
        $this->usingFeatures = true;
        $this->features->save($feature, false);
    }

    /**
     * @Given /^I lose my session$/
     */
    public function iLoseMySession()
    {
        $session = $this->getMinkContext()->getSession();
        $session->restart();
        // set unknown session id to prevent session not found exception
        $session->setCookie(session_name(), '000000');
    }
    /**
     * @Given /^I lose my session and reload$/
     */
    public function iLoseMySessionAndReload()
    {
        $session = $this->getMinkContext()->getSession();
        $currentUrl = $session->getCurrentUrl();
        $session->restart();
        // set unknown session id to prevent session not found exception
        $session->setCookie(session_name(), '000000');
        $session->visit($currentUrl);
    }

    /**
     * @Given /^pdp gives a deny response$/
     */
    public function pdpGivesADenyResponse()
    {
        $this->usingPdp = true;
        $this->pdpClient->receiveDenyResponse();
    }

    /**
     * @Given /^pdp gives an IdP specific deny response for "([^"]*)"$/
     */
    public function pdpGivesAnIdpSpecificDenyResponse($idpName)
    {
        $this->usingPdp = true;
        $this->pdpClient->receiveSpecificDenyResponse($idpName);
    }

    /**
     * @Given /^pdp gives an indeterminate response$/
     */
    public function pdpGivesAnIndeterminateResponse()
    {
        $this->usingPdp = true;
        $this->pdpClient->receiveIndeterminateResponse();
    }

    /**
     * @Given /^pdp gives a permit response$/
     */
    public function pdpGivesAnPermitResponse()
    {
        $this->usingPdp = true;
        $this->pdpClient->receivePermitResponse();
    }

    /**
     * @Given /^pdp gives a not applicable response$/
     */
    public function pdpGivesANotApplicableResponse()
    {
        $this->usingPdp = true;
        $this->pdpClient->receiveNotApplicableResponse();
    }

    /**
     * @Given /^EngineBlock is configured to allow a maximum of (\d+) authentication procedures within a time frame of (\d+) seconds$/
     * @param int $timeFrameForAuthenticationLoopInSeconds
     * @param int $maximumAuthenticationProceduresAllowed
     */
    public function engineblockIsConfiguredToAllowAMaximumOfAuthenticationProceduresWithinATimeFrameOfSeconds(
        $maximumAuthenticationProceduresAllowed,
        $timeFrameForAuthenticationLoopInSeconds
    ) {
        $this->authenticationLoopGuard->saveAuthenticationLoopGuardConfiguration(
            (int) $maximumAuthenticationProceduresAllowed,
            (int) $timeFrameForAuthenticationLoopInSeconds
        );
        $this->usingAuthenticationLoopGuard = true;
    }

    /**
     * @AfterScenario
     */
    public function cleanAttributeAggregator()
    {
        if ($this->usingAttributeAggregationClient) {
            $this->attributeAggregationClient->returnsNothing();
        }
    }

    /**
     * @AfterScenario
     */
    public function cleanUpPdp()
    {
        if ($this->usingPdp) {
            $this->pdpClient->clear();
        }
    }

    /**
     * @AfterScenario
     */
    public function cleanUpFeatures()
    {
        if ($this->usingFeatures) {
            $this->features->clean();
        }
    }

    /**
     * @AftectScenario
     */
    public function cleanUpAuthenticationLoopGuard()
    {
        if ($this->usingAuthenticationLoopGuard) {
            $this->authenticationLoopGuard->cleanUp();
        }
    }

    /**
     * @Then /^the AuthnRequest to submit should match xpath '([^']*)'$/
     */
    public function theAuthnRequestToSubmitShouldMatchXpath($xpath)
    {
        $session = $this->getMinkContext()->getSession();
        $mink    = $session->getPage();

        $authnRequestElement = $mink->find('css', 'input[name="authnRequestXml"]');
        if ($authnRequestElement === null) {
            throw new ExpectationException('Element with the name "authnRequestXml" could not be found', $session);
        }

        $authnRequestXml = html_entity_decode($authnRequestElement->getValue());

        /**
         * @see MinkContext::theResponseShouldMatchXpath()
         */
        $authnRequest = new DOMDocument();
        $authnRequest->loadXML($authnRequestXml);

        $xpathObject = new DOMXPath($authnRequest);
        $nodeList = $xpathObject->query($xpath);

        if (!$nodeList || $nodeList->length === 0) {
            $message = sprintf('The xpath "%s" did not result in at least one match.', $xpath);
            throw new ExpectationException($message, $session);
        }
    }

    /**
     * @Given /^my browser is configured to accept language "([^"]*)"$/
     */
    public function myBrowserIsConfiguredToAcceptLanguage($language)
    {
        $this->getMinkContext()->getSession()->setRequestHeader('Accept-Language', $language);
    }

    /**
     * @Then /^a lang cookie should be set with value "([^"]*)"$/
     */
    public function aLangCookieShouldBeSetWithValue($locale)
    {
        $cookie = $this->getMinkContext()->getSession()->getCookie('lang');

        if ($cookie === null) {
            throw new ExpectationException(
                'The lang cookie has not been set',
                $this->getMinkContext()->getSession()->getDriver()
            );
        }

        if ($cookie !== $locale) {
            throw new ExpectationException(
                sprintf('The lang cookie should contain "%s", but contains "%s"', $locale, $cookie),
                $this->getMinkContext()->getSession()->getDriver()
            );
        }
    }

    /**
     * @Given /^I have a locale cookie containing "([^"]*)"$/
     */
    public function iHaveALocaleCookieContaining($locale)
    {
        $this->getMinkContext()->getSession()->setCookie('lang', $locale);
    }

    /**
     * @When /^I go to Engineblock URL "([^"]*)"$/
     */
    public function iGoToEngineblockURL($path)
    {
        $this->getMinkContext()->visit($this->engineBlockDomain . $path);
    }

    /**
     * @When /^I post data "([^"]*)" to Engineblock URL "([^"]*)"$/
     */
    public function iPostDataToEngineBlockUrl($data, $path)
    {
        $postdata = json_decode($data, true);
        $url = $this->engineBlockDomain . $path;

        $this->getMinkContext()->getSession()->getDriver()->getClient()->request('POST', $url, $postdata);
    }

    /**
     * @Given /^the attribute aggregator returns no attributes$/
     */
    public function aaReturnsNoAttributes()
    {
        $this->usingAttributeAggregationClient = true;

        $this->attributeAggregationClient->returnsNothing();
    }

    /**
     * @Given /^the attribute aggregator returns the attributes:$/
     */
    public function aaReturnsAttributes(TableNode $attributes)
    {
        $this->usingAttributeAggregationClient = true;

        foreach ($attributes->getHash() as $attribute) {
            $this->attributeAggregationClient->returnsAttribute(
                $attribute['Name'],
                explode(',', $attribute['Value']),
                $attribute['Source']
            );
        }
    }

    /**
     * @Given /^I should see ART code "([^"]*)"$/
     */
    public function iShouldSeeARTCode($artCode)
    {
        $session = $this->getMinkContext()->getSession();
        $mink = $session->getPage();
        // Grab the ART code from the page with an xpath expression.
        $result = $mink->find('xpath', '//span[text()="EC:"]/../span[2]');
        if ($result) {
            $artOnPage = $result->getText();
            if ($artOnPage == $artCode) {
                return;
            }
            throw new RuntimeException(
                sprintf('Expected Error Code "%s" did not match the Error Code on the page "%s"', $artCode, $artOnPage)
            );
        }
        throw new RuntimeException('Unable to find the Error Code on the page');
    }

    /**
     * @Then /^I write down the request id as seen on the error page$/
     */
    public function iWriteDownTimestampAndRequestId()
    {
        $this->currentRequestId = $this->getRequestIdFromFeedbackInformation();
    }

    /**
     * @Then /^I should see the same request id on the error page$/
     */
    public function iShouldSeeTheSameRequestId()
    {
        $actualRequestId = $this->getRequestIdFromFeedbackInformation();
        if ($actualRequestId !== $this->currentRequestId) {
            throw new RuntimeException(
                sprintf(
                    'The request id changed between requests: "%s" versus "%s"',
                    $actualRequestId,
                    $this->currentRequestId
                )
            );
        }
        return;
    }
    /**
     * @Then /^I should not see the same request id on the error page$/
     */
    public function iShouldNotSeeTheSameRequestId()
    {
        try {
            // Not being able to find the request id yields a runtime exception
            $this->getRequestIdFromFeedbackInformation();
        } catch (RuntimeException $e) {
            return;
        }

        throw new RuntimeException('The request was found on the page, and we expected it not to be.');
    }

    /**
     * Reads the request id from the error feedback page and returns it as a string
     */
    private function getRequestIdFromFeedbackInformation()
    {
        $session = $this->getMinkContext()->getSession();
        $mink = $session->getPage();
        // Grab the request id from the page with an xpath expression.
        $result = $mink->find('xpath', '//span[text()="UR ID:"]/../span[2]');
        if ($result) {
            $requestIdOnPage = $result->getText();
            if ($requestIdOnPage && $requestIdOnPage !== '') {
                return $requestIdOnPage;
            }
        }
        throw new RuntimeException('Unable to find the request id on the page');
    }
}
