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

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProviderFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Service\EngineBlock;
use SAML2\AuthnRequest;
use SAML2\XML\saml\Issuer;

/**
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

        $this->getMinkContext()->visit($ssoStartLocation);
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
     * @Given /^SP "([^"]*)" does not require consent$/
     */
    public function spDoesNotRequireConsent($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setSpEntityNoConsent($mockSp->entityId())
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

    /**
     * @Given /^SP "([^"]*)" is set with acs location "([^"]*)"$/
     */
    public function spConfiguredWithAcsLocation($spName, $acsLocation)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $request = new AuthnRequest();
        $issuer = new Issuer();
        $issuer->setValue($sp->entityId());
        $request->setIssuer($issuer);
        $request->setAssertionConsumerServiceURL($acsLocation);
        $sp->setAuthnRequest($request);

        $this->mockSpRegistry->save();
    }
    /**
     * @Given /^SP "([^"]*)" requests LoA "([^"]*)"$/
     */
    public function spRequestsLoa($spName, $loa)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $request = new AuthnRequest();
        $issuer = new Issuer();
        $issuer->setValue($sp->entityId());
        $request->setRelayState('RelayState jonguh');
        $request->setIssuer($issuer);
        $request->setAssertionConsumerServiceURL($sp->assertionConsumerServiceLocation());
        $request->setRequestedAuthnContext(['AuthnContextClassRef' => [$loa]]);
        $sp->setAuthnRequest($request);

        $this->mockSpRegistry->save();
    }

    private function doSpSignsItsRequests(MockServiceProvider $sp)
    {
        $sp->signAuthnRequests();

        $this->mockSpRegistry->save();

        $this->serviceRegistryFixture
            ->setSpEntityWantsSignature($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" is a trusted proxy$/
     */
    public function spIsATrustedProxy($spName)
    {
        $this->serviceRegistryFixture->setSpEntityTrustedProxy(
            $this->mockSpRegistry->get($spName)->entityid()
        );
        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^SP "([^"]*)" requires a RequesterId$/
     */
    public function spRequiresARequesterId($spName)
    {
        $this->serviceRegistryFixture->setSpEntityRequesterIdRequired(
            $this->mockSpRegistry->get($spName)->entityid()
        );
        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^SP "([^"]*)" is not connected to IdP "([^"]*)"$/
     */
    public function disconnectSpToIdp($spName, $idpName)
    {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);
        /** @var MockServiceProvider $mockSp */
        $mockSp  = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture->disconnectSp($mockSp->entityId(), $mockIdp->entityId());

        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^the SP uses the HTTP POST Binding$/
     */
    public function theOnlySpUsesHttpPostBinding()
    {
        $sp = $this->mockSpRegistry->getOnly();

        $sp->useHttpPost();

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^the SP "([^"]*)" uses the HTTP POST Binding$/
     */
    public function theSpUsesHttpPostBinding($spName)
    {
        $sp = $this->mockSpRegistry->get($spName);

        $sp->useHttpPost();

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^the SP "([^"]*)" sends a malformed AuthNRequest$/
     */
    public function theSpSendsMalformedAuthnRequest($spName)
    {
        $sp = $this->mockSpRegistry->get($spName);

        $sp->sendMalformedAuthNRequest();

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
        // Travis / PHP 5.6 issue requires gc cycle in order to actually clear the fixture
        // https://www.pivotaltracker.com/story/show/161282428
        gc_collect_cycles();
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
     * @Given /^SP "([^"]*)" is authenticating for SP "([^"]*)" resetting the RequesterID chain$/
     */
    public function spIsAuthenticatingForSpReset($spName, $spDestinationName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);
        /** @var MockServiceProvider $spDestination */
        $spDestination = $this->mockSpRegistry->get($spDestinationName);

        $authNRequest = $sp->getAuthnRequest();
        $requesterIds = [];
        $requesterIds[] = $spDestination->entityId();
        $authNRequest->setRequesterID($requesterIds);

        $this->mockSpRegistry->save();
    }


    /**
     * @Given /^SP "([^"]*)" is authenticating for misconfigured SP "([^"]*)"$/
     */
    public function spIsAuthenticatingForMisconfiguredSp($spName, $spDestinationName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);
        /** @var MockServiceProvider $spDestination */
        $spDestination = $this->mockSpRegistry->get($spDestinationName);

        $authNRequest = $sp->getAuthnRequest();
        $requesterIds = $authNRequest->getRequesterID();
        $requesterIds[] = $spDestination->entityId() . '-i-do-not-exist';
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
        $mink = $this->getMinkContext();
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
            ->setSpEntityManipulation($sp->entityId(), $manipulation->getRaw())
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
            ->allowNoAttributeValuesForSp($sp->entityId())
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
            ->allowAttributeValueForSp($sp->entityId(), $arpAttribute, "*")
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
            ->allowAttributeValueForSp($sp->entityId(), $arpAttribute, $arpAttributeValue)
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" allows an attribute named "([^"]*)" released as "([^"]*)"$/
     **/
    public function spAllowsAnAttributeNamedReleasedAs($spName, $arpAttribute, $releaseAs)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->allowAttributeReleasedAsForSp($sp->entityId(), $arpAttribute, $releaseAs)
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" uses the value of attribute "([^"]*)" as the NameId$/
     **/
    public function spOverridesNameId($spName, $attributeName)
    {
        /** @var MockServiceProvider $sp */
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->substituteNameIdWithAttributeValue($sp->entityId(), $attributeName)
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
            ->allowAttributeValueForSp($sp->entityId(), $arpAttribute, "*", $attributeSource)
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
            $motivation = null;

            if (isset($attribute['Motivation'])) {
                $motivation = $attribute['Motivation'];
            }

            $this->serviceRegistryFixture
                ->allowAttributeValueForSp($sp->entityId(), $attribute['Name'], $attribute['Value'], $attribute['Source'], $motivation)
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
            ->setSpEntityNameIdFormatUnspecified($sp->entityId())
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
            ->setSpEntityNameIdFormatPersistent($sp->entityId())
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
            ->setSpEntityNameIdFormatTransient($sp->entityId())
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
            ->setSpWorkflowState($sp->entityId(), $workflowState)
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
            ->spRequiresPolicyEnforcementDecisionForSp($sp->entityId())
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
            ->requireAttributeAggregationForSp($mockSp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" requires a signed response$/
     */
    public function spRequiresASignedResponse($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setSpSignRepsones($mockSp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" does not require a signed response$/
     */
    public function spDoesNotRequireASignedResponse($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setSpSignRepsones($mockSp->entityId(), false)
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" is configured to display unconnected IdPs in the WAYF$/
     * @param string $spName
     */
    public function spIsConfiguredToDisplayUnconnectedIdps($spName)
    {
        $sp = $this->anUnregisteredServiceProviderNamed($spName);

        $this->serviceRegistryFixture
            ->displayUnconnectedIdpsForSp($sp->entityId())
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" is configured to only display connected IdPs in the WAYF$/
     * @param string $spName
     */
    public function spIsConfiguredToDisplayOnlyConnectedIdps($spName)
    {
        $sp = $this->anUnregisteredServiceProviderNamed($spName);

        $this->serviceRegistryFixture
            ->displayUnconnectedIdpsForSp($sp->entityId(), false)
            ->save();
    }

    /**
     * @Given /^SP "([^"]*)" scopes its request to IDP "([^"]*)"$/
     */
    public function spAuthnRequestScopedToIdp($spName, $idpName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);
        $mockIdp = $this->mockIdpRegistry->get($idpName);

        $mockSp->addIdpToScope($mockIdp->entityId());

        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^the SP "([^"]*)" sends AuthnContextClassRef with value "([^"]*)"$/
     */
    public function theSPSendsAuthnContextClassRefWithValue($spName, $authnContextClassRefValue)
    {
        $mockSp = $this->mockSpRegistry->get($spName);
        $request = $mockSp->getAuthnRequest();
        $request->setRequestedAuthnContext(['AuthnContextClassRef' => [$authnContextClassRefValue]]);
        $this->mockSpRegistry->save();
    }

    /**
     * @Given /^the SP "([^"]*)" sends no AuthnContextClassRef$/
     */
    public function theSPSendsNoAuthnContextClassRef(string $spName): void
    {
        $mockSp = $this->mockSpRegistry->get($spName);
        $request = $mockSp->getAuthnRequest();
        $request->setRequestedAuthnContext();
        $this->mockSpRegistry->save();
    }
}
