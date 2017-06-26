<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use EngineBlock_Saml2_IdGenerator;
use OpenConext\EngineBlockFunctionalTestingBundle\Parser\LogChunkParser;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\IdFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Service\EngineBlock;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProviderFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;

/**
 * Class MockSpContext
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Features\Context
 * @SuppressWarnings("PMD")
 */
class MockSpContext extends AbstractSubContext
{
    /**
     * @var EntityRegistry
     */
    protected $mockSpRegistry;

    /**
     * @var EntityRegistry
     */
    protected $mockIdpRegistry;

    /**
     * @var ServiceRegistryFixture
     */
    protected $serviceRegistryFixture;

    /**
     * @var MockServiceProviderFactory
     */
    protected $mockSpFactory;

    /**
     * @var EngineBlock
     */
    protected $engineBlock;

    /**
     * @param ServiceRegistryFixture $serviceRegistryFixture
     * @param EngineBlock $engineBlock
     * @param MockServiceProviderFactory $mockSpFactory
     * @param EntityRegistry $mockSpRegistry
     * @param EntityRegistry $mockIdpRegistry
     */
    public function __construct(
        ServiceRegistryFixture $serviceRegistryFixture,
        EngineBlock $engineBlock,
        MockServiceProviderFactory $mockSpFactory,
        EntityRegistry $mockSpRegistry,
        EntityRegistry $mockIdpRegistry
    ) {
        $this->serviceRegistryFixture = $serviceRegistryFixture;
        $this->engineBlock = $engineBlock;
        $this->mockSpFactory = $mockSpFactory;
        $this->mockSpRegistry = $mockSpRegistry;
        $this->mockIdpRegistry = $mockIdpRegistry;
    }

    /**
     * @When /^I log in at SP "([^"]*)" which attempts to preselect nonexistent IdP "([^"]*)"$/
     */
    public function iLogInAtSPWhichAttemptsToPreselectNonexistentIdP($spName, $idpName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $mockSp->useIdpTransparently($idpName);

        $this->mockSpRegistry->save();

        $this->iTriggerTheLoginEitherAtOrUnsolicitedAtEb($spName);
    }


    /**
     * @When /^I trigger the login \(either at "([^"]*)" or unsolicited at EB\)$/
     */
    public function iTriggerTheLoginEitherAtOrUnsolicitedAtEb($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        if ($mockSp->mustUseUnsolicited()) {
            $ssoStartLocation = $this->engineBlock->unsolicitedLocation($mockSp->entityId());
        } elseif ($mockSp->mustUsePost()) {
            $ssoStartLocation = $mockSp->loginUrlPost();
        } else {
            $ssoStartLocation = $mockSp->loginUrlRedirect();
        }

        $this->getMainContext()->getMinkContext()->visit($ssoStartLocation);
    }

    /**
     * @Given /^a Service Provider named "([^"]*)"$/
     */
    public function aServiceProviderNamedWithEntityid($name)
    {
        $mockSp = $this->anUnregisteredServiceProviderNamed($name);
        $this->serviceRegistryFixture->registerSp(
            $name,
            $mockSp->entityId(),
            $mockSp->assertionConsumerServiceLocation(),
            $mockSp->publicKeyCertData()
        )->save();
    }

    /**
     * @Given /^an unregistered Service Provider named "([^"]*)"$/
     */
    public function anUnregisteredServiceProviderNamed($name)
    {
        $mockSp = $this->mockSpFactory->createNew($name);
        $this->mockSpRegistry->set($name, $mockSp);
        $this->mockSpRegistry->save();
        return $mockSp;
    }

    /**
     * @Given /^SP "([^"]*)" may only access "([^"]*)"$/
     */
    public function spMayOnlyAccess($spName, $idpName)
    {
        $spEntityId = $this->mockSpRegistry->get($spName)->entityId();

        $idpEntityId = $this->mockIdpRegistry->get($idpName)->entityId();

        $this->serviceRegistryFixture
            ->blacklist($spEntityId)
            ->allow($spEntityId, $idpEntityId)
            ->save();

        // Override the Destination for the Response
        $this->mockIdpRegistry->get($idpName)->overrideResponseDestination(
            $this->engineBlock->assertionConsumerLocation()
        );
        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^SP "([^"]*)" is configured to generate a AuthnRequest like the one at "([^"]*)"$/
     */
    public function spIsConfiguredToGenerateAAuthnrequestLikeTheOneAt($spName, $authnRequestLogFile)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $mockSpDefaultEntityId = $mockSp->entityId();
        $mockSpAcsLocation     = $mockSp->assertionConsumerServiceLocation();

        // First see if the request was even triggered by the SP, or if it was an unsolicited request
        // by EB.
        $logReader = new LogChunkParser($authnRequestLogFile);
        $unsolicitedRequest = $logReader->detectUnsolicitedRequest();
        if ($unsolicitedRequest) {
            $this->printDebug("Unsolicited Request:" . PHP_EOL . print_r($unsolicitedRequest, true));

            $mockSp->useUnsolicited();

            $requestIssuer = $unsolicitedRequest['saml:Issuer']['__v'];

            $frame = $this->engineBlock->getIdsToUse(IdFixture::FRAME_REQUEST);
            $frame->set(EngineBlock_Saml2_IdGenerator::ID_USAGE_SAML2_REQUEST, $unsolicitedRequest['_ID']);
        } else {
            // If not, then parse an AuthnRequest out of the log file
            $authnRequest = $logReader->getMessage(LogChunkParser::MESSAGE_TYPE_AUTHN_REQUEST);
            $mockSp->setAuthnRequest($authnRequest);
            $this->printDebug(print_r($authnRequest, true));

            $requestIssuer = $authnRequest->getIssuer();
        }

        // Listen up Mock Service Provider, you must now pretend that you are the issuer of the request.
        $mockSp->setEntityId($requestIssuer);

        $this->mockSpRegistry->save();

        // Override the ACS Location for the SP used in the response to go to the Mock SP
        $this->serviceRegistryFixture
            ->remove($mockSpDefaultEntityId)
            ->setEntityAcsLocation($requestIssuer, $mockSpAcsLocation)
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" may run in transparent mode, if indicated in "([^"]*)"$/
     */
    public function spMayRunInTransparentModeIfIndicatedIn($spName, $sessionLogFIle)
    {
        $logReader = new LogChunkParser($sessionLogFIle);
        $entityId = $logReader->detectTransparentRequest();

        if (!$entityId) {
            return;
        }

        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);
        $mockSp->useIdpTransparently($entityId);

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^SP "([^"]*)" does not require consent$/
     */
    public function spDoesNotRequireConsent($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setEntityNoConsent($mockSp->entityId())
            ->save();
    }

    /**
     * @When /^I log in at "([^"]*)"$/
     */
    public function iLogInAt($spName)
    {
        $this->iTriggerTheLoginEitherAtOrUnsolicitedAtEb($spName);
    }

    /**
     * @Given /^the SP signs its requests$/
     */
    public function theSpSignsItSRequests()
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->getOnly();
        $this->doSpSignsItsRequests($sp);
    }

    /**
     * @Given /^SP "([^"]*)" signs its requests$/
     */
    public function spSignsItSRequests($spName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);
        $this->doSpSignsItsRequests($sp);
    }

    private function doSpSignsItsRequests(MockServiceProvider $sp)
    {
        $sp->signAuthnRequests();

        $this->mockSpRegistry->save();

        $this->serviceRegistryFixture
            ->setEntityWantsSignature($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" is a trusted proxy$/
     */
    public function spIsATrustedProxy($spName)
    {
        $this->serviceRegistryFixture->setEntityTrustedProxy(
            $this->mockSpRegistry->get($spName)->entityid()
        );
        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^SP "([^"]*)" uses a blacklist for access control$/
     */
    public function spUsesABlacklistOfAccessControl($spName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);
        $this->serviceRegistryFixture
            ->blacklist($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" uses a whitelist for access control$/
     */
    public function spUsesAWhitelistForAccessControl($spName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);
        $this->serviceRegistryFixture
            ->whitelist($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" whitelists IdP "([^"]*)"$/
     */
    public function spWhitelistsIdp($spName, $idpName)
    {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);
        /** @var MockServiceProvider $mockSp */
        $mockSp  = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture->allow($mockSp->entityid(), $mockIdp->entityId());

        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^the SP uses the HTTP POST Binding$/
     */
    public function theSpUsesTheHttpPostBinding()
    {
        $sp = $this->mockSpRegistry->getOnly();

        $sp->useHttpPost();

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^the SP uses the HTTP Redirect Binding$/
     */
    public function theSpUsesTheHttpRedirectBinding()
    {
        $sp = $this->mockSpRegistry->getOnly();

        $sp->useHttpRedirect();

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^no registered SPs/
     */
    public function noRegisteredServiceProviders()
    {
        $this->mockSpRegistry->clear()->save();
    }

    /**
     * @Given /^SP "([^"]*)" is authenticating for SP "([^"]*)"$/
     */
    public function spIsAuthenticatingForSp($spName, $spDestinationName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);
        /** @var MockServiceProvider $spDestination */
        $spDestination = $this->mockSpRegistry->get($spDestinationName);

        $authNRequest = $sp->getAuthnRequest();
        $requesterIds = $authNRequest->getRequesterID();
        $requesterIds[] = $spDestination->entityId();
        $authNRequest->setRequesterID($requesterIds);

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^SP "([^"]*)" is authenticating and uses RequesterID "([^"]*)"$/
     */
    public function spIsAuthenticatingAndUsesRequesterid($spName, $requesterEntityId)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $sp->getAuthnRequest()->setRequesterID([$requesterEntityId]);

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^I pass through the SP$/
     */
    public function iPassThroughTheSp()
    {
        $mink = $this->getMainContext()->getMinkContext();
        $mink->pressButton('GO');
    }

    /**
     * @Given /^SP "([^"]*)" has the following Attribute Manipulation:$/
     */
    public function spHasTheFollowingAttributeManipulation($spName, PyStringNode $manipulation)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setEntityManipulation($sp->entityId(), $manipulation->getRaw())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" allows no attributes$/
     */
    public function spAllowsNoAttributes($spName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->allowNoAttributeValues($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" allows an attribute named "([^"]*)"$/
     */
    public function spAllowsAnAttributeNamed($spName, $arpAttribute)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->allowAttributeValue($sp->entityId(), $arpAttribute, "*")
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" allows an attribute named "([^"]*)" with value "([^"]*)"$/
     **/
    public function spAllowsAnAttributeNamedWithValue($spName, $arpAttribute, $arpAttributeValue)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->allowAttributeValue($sp->entityId(), $arpAttribute, $arpAttributeValue)
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" allows an attribute named "([^"]*)" and configures it for aggregation from "([^"]*)"$/
     */
    public function spAllowsAnAttributeWithNameFromSource($spName, $arpAttribute, $attributeSource)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->allowAttributeValue($sp->entityId(), $arpAttribute, "*", $attributeSource)
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" allows the following attributes:$/
     */
    public function spAllowsGivenAttributes($spName, TableNode $attributes)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        foreach ($attributes->getHash() as $attribute) {
            $this->serviceRegistryFixture
                ->allowAttributeValue($sp->entityId(), $attribute['Name'], $attribute['Value'], $attribute['Source'])
                ->save();
        }
    }

    /**
     * @Given /^SP "([^"]*)" uses the Unspecified NameID format$/
     */
    public function spUsesTheUnspecifiedNameidFormat($spName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setEntityNameIdFormatUnspecified($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" uses the Persistent NameID format$/
     */
    public function spUsesThePersistentNameidFormat($spName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setEntityNameIdFormatPersistent($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" uses the Transient NameID format$/
     */
    public function spUsesTheTransientNameidFormat($spName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setEntityNameIdFormatTransient($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" is using workflow state "([^"]*)"$/
     */
    public function spUsesStatus($spName, $workflowState)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setWorkflowState($sp->entityId(), $workflowState)
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" is configured to generate a AuthnRequest with a ProxyCount of (\d+)$/
     * @param string $spName
     * @param int $proxyCount
     */
    public function spIsConfiguredToGenerateAAuthnRequestWithAProxyCountOf($spName, $proxyCount)
    {
        $sp = $this->mockSpRegistry->get($spName);
        $sp->setAuthnRequestProxyCountTo((int) $proxyCount);

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^SP "([^"]*)" is configured to generate a passive AuthnRequest$/
     * @param $spName
     */
    public function spIsConfiguredToGenerateAPassiveAuthnRequest($spName)
    {
        $sp = $this->mockSpRegistry->get($spName);
        $sp->setAuthnRequestToPassive();

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^SP "([^"]*)" requires a policy enforcement decision$/
     * @param string $spName
     */
    public function spRequiresAPolicyEnforcementDecision($spName)
    {
        $sp = $this->anUnregisteredServiceProviderNamed($spName);

        $this->serviceRegistryFixture
            ->spRequiresPolicyEnforcementDecision($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" requires attribute aggregation$/
     * @param string $spName
     */
    public function spRequiresAttributeAggregation($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->requireAttributeAggregation($mockSp->entityId())
            ->save();
    }
}
