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

use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\Discovery;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProviderFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Service\EngineBlock;

/**
 * Class MockIdpContext
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Features\Context
 * @SuppressWarnings("PMD")
 */
class MockIdpContext extends AbstractSubContext
{
    /**
     * @var ServiceRegistryFixture
     */
    protected $serviceRegistryFixture;

    /**
     * @var EngineBlock
     */
    protected $engineBlock;

    /**
     * @var MockIdentityProviderFactory
     */
    protected $mockIdpFactory;

    /**
     * @var EntityRegistry
     */
    protected $mockIdpRegistry;

    /**
     * @var \OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry
     */
    protected $mockSpRegistry;

    /**
     * @param ServiceRegistryFixture $serviceRegistryFixture
     * @param EngineBlock $engineBlock
     * @param MockIdentityProviderFactory $idpFactory
     * @param EntityRegistry $mockIdpRegistry
     * @param EntityRegistry $mockSpRegistry
     */
    public function __construct(
        ServiceRegistryFixture $serviceRegistryFixture,
        EngineBlock $engineBlock,
        MockIdentityProviderFactory $idpFactory,
        EntityRegistry $mockIdpRegistry,
        EntityRegistry $mockSpRegistry
    ) {
        $this->serviceRegistryFixture = $serviceRegistryFixture;
        $this->engineBlock = $engineBlock;
        $this->mockIdpFactory = $idpFactory;
        $this->mockIdpRegistry = $mockIdpRegistry;
        $this->mockSpRegistry = $mockSpRegistry;
    }

    /**
     * @Given /^an Identity Provider named "([^"]*)"$/
     * @Given /^an Identity Provider named "([^"]*)" with discovery "([^"]*)"$/
     */
    public function anIdentityProviderNamed($name, string $discoveryName = null)
    {
        $discoveries = [];
        if ($discoveryName !== null) {
            $discoveries[] = Discovery::create(['en' => $discoveryName], [], null);
        }

        $mockIdp = $this->mockIdpFactory->createNew($name);
        $this->mockIdpRegistry->set($name, $mockIdp);
        $this->mockIdpRegistry->save();
        $this->serviceRegistryFixture->registerIdp(
            $name,
            $mockIdp->entityId(),
            $mockIdp->singleSignOnLocation(),
            $mockIdp->publicKeyCertData(),
            $discoveries
        )->save();
    }

    /**
     * @Given /^an Identity Provider named "([^"]*)" with logo "([^"]*)"$/
     */
    public function anIdentityProviderNamedWithLogo($name, $logo)
    {
        $this->anIdentityProviderNamed($name);
        $mockIdp = $this->mockIdpRegistry->get($name);
        $this->serviceRegistryFixture->setIdpLogo($mockIdp->entityId(), $logo)->save();
    }

    /**
     * @Given /^the IdP is configured to always return Responses with StatusCode (\w+)\/(\w+)$/
     */
    public function theIdpIsConfiguredToAlwaysReturnResponsesWithStatuscode($topStatusCode, $secondStatusCode)
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();
        $idp->setStatusCode($topStatusCode, $secondStatusCode);
        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP is configured to always return Responses with StatusMessage "([^"]*)"$/
     */
    public function theIdpIsConfiguredToAlwaysReturnResponsesWithStatusmessage($statusMessage)
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();
        $idp->setStatusMessage($statusMessage);
        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP uses the private key at "([^"]*)"$/
     */
    public function theIdpUsesThePrivateKeyAt($privateKeyFile)
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();
        $idp->setPrivateKey($privateKeyFile);
        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP uses the certificate at "([^"]*)"$/
     */
    public function theIdpUsesTheCertificateAt($publicKeyFile)
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();
        $idp->setCertificate($publicKeyFile);
        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP thinks its EntityID is "([^"]*)"$/
     */
    public function theIdpThinksItsEntityidIs($entityId)
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();

        $idp->setEntityId($entityId);

        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP uses the HTTP Redirect Binding$/
     */
    public function theIdpUsesTheHttpRedirectBinding()
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();

        $idp->useHttpRedirect();

        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP does not send the attribute named "([^"]*)"$/
     */
    public function theIdpDoesNotSendTheAttributeNamed($attributeName)
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();

        $idp->removeAttribute($attributeName);

        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP does not sign its responses$/
     */
    public function theIdpDoesNotSignItsResponses()
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();

        $idp->doNotUseResponseSigning();

        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP encrypts its assertions with the public key in "([^"]*)"$/
     */
    public function theIdpEncryptsItsAssertionsWithThePublicKeyIn($certFilePath)
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();

        $idp->useEncryptionCert($certFilePath);

        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP encrypts its assertions with the shared key "([^"]*)"$/
     */
    public function theIdpEncryptsItsAssertionsWithTheSharedKey($sharedKey)
    {
        /** @var MockIdentityProvider $idp */
        $idp = $this->mockIdpRegistry->getOnly();

        $idp->useEncryptionSharedKey($sharedKey);

        $this->mockIdpRegistry->save();
    }


    /**
     * @Given /^the Idp with name "([^"]*)" has shibd scope "([^"]*)"$/
     */
    public function theIdpWithNameHasShibdScope($name, $scope)
    {
        $mockIdp = $this->mockIdpRegistry->get($name);
        $this->serviceRegistryFixture->setIdpScope($mockIdp->entityId(), $scope)->save();
    }

    /**
     * @Given /^no registered Idps/
     */
    public function noRegisteredIdentityProviders()
    {
        $this->mockIdpRegistry->clear()->save();
    }

    /**
     * @Given /^I pass through the IdP$/
     */
    public function iPassThroughTheIdp()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('GO');
    }

    /**
     * @Given /^the IdP is configured to not send an Assertion$/
     */
    public function theIdPIsConfiguredToNotSendAnAssertion()
    {
        $idp = $this->mockIdpRegistry->getOnly();

        $idp->doNotSendAssertions();

        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP "([^"]*)" sends attribute "([^"]*)" with value "([^"]*)"$/
     * @param string $idpName
     * @param string $attributeName
     * @param string $attributeValue
     */
    public function theIdPSendsAttributeWithValue($idpName, $attributeName, $attributeValue)
    {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);

        $mockIdp->setAttribute($attributeName, [$attributeValue]);

        $this->mockIdpRegistry->save();
    }

    /**
     * Please provide the attribute values in a a comma separated manner.
     *
     * @Given /^the IdP "([^"]*)" sends attribute "([^"]*)" with values "([^"]*)" and xsi:type is "([^"]*)"$/
     * @param string $idpName
     * @param string $attributeName
     * @param $attributeValues
     * @param $attributeValueType
     */
    public function theIdPSendsAttributeWithValuesAndType(
        $idpName,
        $attributeName,
        $attributeValues,
        $attributeValueType
    ) {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);
        $explosion = explode(',', $attributeValues);
        $mockIdp->setAttribute($attributeName, $explosion, $attributeValueType);
        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^the IdP "([^"]*)" sends AuthnContextClassRef with value "([^"]*)"$/
     * @param string $idpName
     * @param string $authnContextClassRefValue
     */
    public function theIdPSendsAuthnContextClassRefValue($idpName, $authnContextClassRefValue)
    {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);

        $mockIdp->setAuthnContextClassRef($authnContextClassRefValue);

        $this->mockIdpRegistry->save();
    }


    /**
     * @Given /^the IdP "([^"]*)" requires informational consent for SP "([^"]*)"$/
     * @param string $idpName
     * @param string $spName
     */
    public function theIdpRequiresInformationalConsentForSp($idpName, $spName)
    {
        $idp = $this->mockIdpRegistry->get($idpName);
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture->setConsentSettings($idp->entityId(), $sp->entityId(), ConsentSettings::CONSENT_INFORMATIONAL);
        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^the IdP "([^"]*)" provides a consent message "([^"]*)" for SP "([^"]*)"$/
     * @param string $idpName
     * @param string $message
     * @param string $spName
     */
    public function theIdPProvidesAConsentMessage($idpName, $message, $spName)
    {
        $idp = $this->mockIdpRegistry->get($idpName);
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture->setConsentSettings($idp->entityId(), $sp->entityId(), ConsentSettings::CONSENT_USERCONSENT, $message);
        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^the IdP "([^"]*)" requires default consent for SP "([^"]*)"$/
     * @param string $idpName
     * @param string $spName
     */
    public function theIdpRequiresDefaultConsentForSp($idpName, $spName)
    {
        $idp = $this->mockIdpRegistry->get($idpName);
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture->setConsentSettings($idp->entityId(), $sp->entityId(), ConsentSettings::CONSENT_USERCONSENT);
        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^the IdP "([^"]*)" requires no consent for SP "([^"]*)"$/
     * @param string $idpName
     * @param string $spName
     */
    public function theIdpRequiresNoConsentForSp($idpName, $spName)
    {
        $idp = $this->mockIdpRegistry->get($idpName);
        $sp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture->setConsentSettings($idp->entityId(), $sp->entityId(), ConsentSettings::CONSENT_DISABLED);
        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^the IdP "([^"]*)" is hidden$/
     * @param string $idpName
     * @param string $attributeName
     * @param string $attributeValue
     */
    public function theIdpIsHidden($idpName)
    {
        $idp = $this->mockIdpRegistry->get($idpName);

        $this->serviceRegistryFixture
            ->setHidden($idp->entityId())
            ->save();

        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^The clock on the IdP "([^"]*)" is behind$/
     */
    public function theClockOnTheIdPIsBehind($idpName)
    {
        $idp = $this->mockIdpRegistry->get($idpName);
        $idp->turnBackTheTime();

        $this->mockIdpRegistry->save();
    }

    /**
     * @Given /^The clock on the IdP "([^"]*)" is ahead/
     */
    public function theClockOnTheIdPIsAhead($idpName)
    {
        $idp = $this->mockIdpRegistry->get($idpName);
        $idp->fromTheFuture();

        $this->mockIdpRegistry->save();
    }
}
