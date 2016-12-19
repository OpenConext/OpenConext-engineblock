<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Mink\Exception\ExpectationException;
use DOMDocument;
use DOMXPath;
use EngineBlock_Saml2_IdGenerator;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingFeatureConfiguration;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\IdFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Parser\LogChunkParser;
use OpenConext\EngineBlockFunctionalTestingBundle\Service\EngineBlock;
use RuntimeException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Both set up and tasks can be a lot...
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
     * @var string
     */
    protected $idpsConfigUrl;

    /**
     * @var string
     */
    protected $spsConfigUrl;

    /**
     * @var FunctionalTestingFeatureConfiguration
     */
    private $features;

    /**
     * @var boolean
     */
    private $usingFeatures = false;

    /**
     * @param ServiceRegistryFixture $serviceRegistry
     * @param EngineBlock $engineBlock
     * @param EntityRegistry $mockSpRegistry
     * @param EntityRegistry $mockIdpRegistry
     * @param string $spsConfigUrl
     * @param string $idpsConfigUrl
     * @param FunctionalTestingFeatureConfiguration $features
     */
    public function __construct(
        ServiceRegistryFixture $serviceRegistry,
        EngineBlock $engineBlock,
        EntityRegistry $mockSpRegistry,
        EntityRegistry $mockIdpRegistry,
        $spsConfigUrl,
        $idpsConfigUrl,
        FunctionalTestingFeatureConfiguration $features
    ) {
        $this->serviceRegistryFixture = $serviceRegistry;
        $this->engineBlock = $engineBlock;
        $this->mockSpRegistry = $mockSpRegistry;
        $this->mockIdpRegistry = $mockIdpRegistry;
        $this->spsConfigUrl = $spsConfigUrl;
        $this->idpsConfigUrl = $idpsConfigUrl;
        $this->features = $features;
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
        $this->engineBlock->clearNewIds();
    }

    /**
     * @Given /^an EngineBlock instance configured with JSON data$/
     */
    public function anEngineblockInstanceConfiguredWithJsonData()
    {
        // Add all known IdPs
        $this->serviceRegistryFixture
            ->reset()
            ->addSpsFromJsonExport($this->spsConfigUrl)
            ->addIdpsFromJsonExport($this->idpsConfigUrl)
            ->save();

        $this->engineBlock->clearNewIds();
    }

    /**
     * @Given /^I follow the EB debug screen to the IdP$/
     */
    public function iFollowTheEbDebugScreenToTheIdp()
    {
        // Support for HTTP-Post
        $hasSubmitButton = $this->getMainContext()->getMinkContext()->getSession()->getPage()->findButton('Submit');
        if ($hasSubmitButton) {
            $this->getMainContext()->getMinkContext()->pressButton('submitbutton');
            return;
        }

        // Default to HTTP-Redirect
        $this->getMainContext()->getMinkContext()->clickLink('GO');
    }

    /**
     * @Given /^EngineBlock is expected to send a AuthnRequest like the one at "([^"]*)"$/
     */
    public function engineblockIsExpectedToSendAAuthnrequestLikeTheOneAt($authnRequestLogFile)
    {
        // Parse an AuthnRequest out of the log file
        $logReader = new LogChunkParser($authnRequestLogFile);
        $authnRequest = $logReader->getMessage(LogChunkParser::MESSAGE_TYPE_AUTHN_REQUEST);

        $hostname = parse_url($authnRequest->getIssuer(), PHP_URL_HOST);
        $this->engineBlock->overrideHostname($hostname);

        $frame = $this->engineBlock->getIdsToUse(IdFixture::FRAME_REQUEST);
        $frame->set(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_REQUEST, $authnRequest->getId());
    }

    /**
     * @Given /^EngineBlock is expected to send a Response like the one at "([^"]*)"$/
     */
    public function engineblockIsExpectedToSendAResponseLikeTheOneAt($responseLogFile)
    {
        // Parse an AuthnRequest out of the log file
        $logReader = new LogChunkParser($responseLogFile);
        $response = $logReader->getMessage(LogChunkParser::MESSAGE_TYPE_RESPONSE);
        $responseAssertions = $response->getAssertions();

        $this->engineBlock->getIdsToUse(IdFixture::FRAME_RESPONSE)
        // EB will generate internal responses, for now just let it give all Responses the same id
            ->set(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_RESPONSE, $response->getId())
            ->set(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_ASSERTION, $responseAssertions[0]->getId())
            ->set(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_RESPONSE, $response->getId())
            ->set(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_ASSERTION, $responseAssertions[0]->getId())
            ->set(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_RESPONSE, $response->getId())
            ->set(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_ASSERTION, $responseAssertions[0]->getId());
    }

    /**
     * @Given /^I print the configured ids$/
     */
    public function iPrintTheConfiguredIds()
    {
        $idFixture = $this->engineBlock->getIdFixture();
        $this->printDebug(print_r($idFixture));
    }

    /**
     * @Given /^I pass through EngineBlock$/
     */
    public function iPassThroughEngineblock()
    {
        $mink = $this->getMainContext()->getMinkContext();

        $mink->pressButton('Submit');
    }

    /**
     * @Given /^I give my consent$/
     */
    public function iGiveMyConsent()
    {
        $mink = $this->getMainContext()->getMinkContext();
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

        $mink = $this->getMainContext()->getMinkContext();
        $mink->visit(
            $this->engineBlock->unsolicitedLocation($mockIdP->entityId(), $mockSp->entityId())
        );
    }

    /**
     * @Given /^An IdP initiated Single Sign on for SP "([^"]*)" is incorrectly triggered by IdP "([^"]*)"$/
     */
    public function anIdpInitiatedSingleSignOnForSpIsIncorrectlyTriggeredByIdP($spName, $idpName)
    {
        $mockSp = $this->mockSpRegistry->get($spName);
        $mockIdP = $this->mockIdpRegistry->get($idpName);

        $mink = $this->getMainContext()->getMinkContext();
        $mink->visit(
            $this->engineBlock->unsolicitedLocation($mockIdP->entityId() . 'I made a booboo', $mockSp->entityId())
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
                "Unable to find idp with name '$idpName'"
            );
        }

        $selector = 'input[type="submit"][data-entityid="' . $mockIdp->entityId() . '"]';

        $mink = $this->getMainContext()->getMinkContext()->getSession()->getPage();
        $button = $mink->find('css', $selector);

        if (!$button) {
            throw new RuntimeException(sprintf('Unable to find button with selector "%s"', $selector));
        }

        $button->click();
    }

    /**
     * @Given /^I log out at EngineBlock$/
     */
    public function iLogoutAtEngineBlock()
    {
        $this->getMainContext()->getMinkContext()->visit($this->engineBlock->logoutLocation());
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
     * @AfterScenario
     */
    public function cleanUpFeatures()
    {
        if ($this->usingFeatures) {
            $this->features->clean();
        }
    }

    /**
     * @Then /^the AuthnRequest should match xpath '([^']*)'$/
     */
    public function theAuthnRequestShouldMatchXpath($xpath)
    {
        $session = $this->getMainContext()->getMinkContext()->getSession();
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
}
