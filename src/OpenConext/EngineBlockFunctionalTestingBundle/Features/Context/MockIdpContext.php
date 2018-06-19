<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use OpenConext\EngineBlockFunctionalTestingBundle\Parser\LogChunkParser;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\EncryptedAssertion;
use OpenConext\EngineBlockFunctionalTestingBundle\Service\EngineBlock;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProviderFactory;

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
     */
    public function anIdentityProviderNamed($name)
    {
        $mockIdp = $this->mockIdpFactory->createNew($name);
        $this->mockIdpRegistry->set($name, $mockIdp);
        $this->mockIdpRegistry->save();
        $this->serviceRegistryFixture->registerIdp(
            $name,
            $mockIdp->entityId(),
            $mockIdp->singleSignOnLocation(),
            $mockIdp->publicKeyCertData()
        )->save();
    }

    /**
     * @Given /^an Identity Provider named "([^"]*)" with logo "([^"]*)"$/
     */
    public function anIdentityProviderNamedWithLogo($name, $logo)
    {
        $this->anIdentityProviderNamed($name);
        $mockIdp = $this->mockIdpRegistry->get($name);
        $this->serviceRegistryFixture->setLogo($mockIdp->entityId(), $logo)->save();
    }

    /**
     * @Given /^IdP "([^"]*)" is configured to return a Response like the one at "([^"]*)"$/
     */
    public function idpIsConfiguredToReturnAResponseLikeTheOneAt($idpName, $responseLogFile)
    {
        // Parse a Response out of the log file
        $logReader = new LogChunkParser($responseLogFile);
        $response = $logReader->getMessage(LogChunkParser::MESSAGE_TYPE_RESPONSE);

        $this->printDebug(print_r($response, true));

        // Write out how the IDP should behave
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);
        $mockIdp->setResponse($response);
        $this->mockIdpRegistry->save();

        $ssoUrl = $mockIdp->singleSignOnLocation();

        // Override the SSO Location for the IDP used in the response to go to the Mock Idp
        $this->serviceRegistryFixture
            ->setEntitySsoLocation($response->getIssuer(), $ssoUrl)
            ->save();

        $this->engineBlock->overrideTime($response->getIssueInstant());
    }

    /**
     * @Given /^the IdP uses a blacklist for access control$/
     */
    public function theIdpUsesABlacklistForAccessControl()
    {
        $this->serviceRegistryFixture
            ->blacklist($this->mockIdpRegistry->getOnly()->entityId())
            ->save();
    }

    /**
     * @Given /^IdP "([^"]*)" uses a blacklist for access control$/
     */
    public function idpUsesABlacklist($idpName)
    {
        $this->serviceRegistryFixture
            ->blacklist($this->mockIdpRegistry->get($idpName)->entityId())
            ->save();
    }

    /**
     * @Given /^IdP "([^"]*)" uses a whitelist for access control$/
     */
    public function idpUsesAWhitelist($idpName)
    {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);

        $this->serviceRegistryFixture->whitelist($mockIdp->entityId());

        $this->serviceRegistryFixture->save();
    }

    /**
     * @Given /^IdP "([^"]*)" whitelists SP "([^"]*)"$/
     */
    public function idpWhitelistsSp($idpName, $spName)
    {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture->allow($mockSp->entityid(), $mockIdp->entityId());

        $this->serviceRegistryFixture->save();
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
        $mink = $this->getMainContext()->getMinkContext();
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
}
