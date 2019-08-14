<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingSfoGatewayMockConfiguration;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProviderFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProviderFactory;
use SAML2\Constants;

class SfoContext extends AbstractSubContext
{
    /**
     * @var EntityRegistry
     */
    private $mockSpRegistry;
    /**
     * @var EntityRegistry
     */
    private $mockIdpRegistry;
    /**
     * @var FunctionalTestingSfoGatewayMockConfiguration
     */
    private $gatewayMockConfiguration;
    /**
     * @var MockIdentityProviderFactory
     */
    private $idpFactory;
    /**
     * @var MockServiceProviderFactory
     */
    private $spFactory;

    /**
     * @param EntityRegistry $mockSpRegistry
     * @param EntityRegistry $mockIdpRegistry
     * @param MockIdentityProviderFactory $idpFactory
     * @param MockServiceProviderFactory $spFactory
     * @param FunctionalTestingSfoGatewayMockConfiguration $gatewayMockConfiguration
     */
    public function __construct(
        EntityRegistry $mockSpRegistry,
        EntityRegistry $mockIdpRegistry,
        MockIdentityProviderFactory $idpFactory,
        MockServiceProviderFactory $spFactory,
        FunctionalTestingSfoGatewayMockConfiguration $gatewayMockConfiguration
    ) {
        $this->mockSpRegistry = $mockSpRegistry;
        $this->mockIdpRegistry = $mockIdpRegistry;
        $this->gatewayMockConfiguration = $gatewayMockConfiguration;
        $this->idpFactory = $idpFactory;
        $this->spFactory = $spFactory;
    }

    /**
     * @Given /^SFO is used$/
     */
    public function sfoIsUsed()
    {
        $mockIdp = $this->idpFactory->createNew('sfoHostedIdp');
        $this->gatewayMockConfiguration->setMockIdentityProvider($mockIdp);

        $mockSp = $this->spFactory->createNew('ebSfoSp');
        $this->gatewayMockConfiguration->setMockServiceProvider($mockSp);
    }

    /**
     * @Given /^SFO will successfully verify a user$/
     */
    public function sfoWillsSuccessfullyVerifyAUser()
    {
        $this->gatewayMockConfiguration->unsetMessage();
    }

    /**
     * @Given /^SFO will fail as the user cancelled$/
     */
    public function sfoWillFailAsTheUserCancelled()
    {
        $this->gatewayMockConfiguration->setMessage(Constants::STATUS_RESPONDER, Constants::STATUS_AUTHN_FAILED, 'Authentication cancelled by user');
    }

    /**
     * @Given /^SFO will fail as the loa can not be given$/
     */
    public function sfoWillFailAsTheLoaCanNotBeGiven()
    {
        $this->gatewayMockConfiguration->setMessage(Constants::STATUS_RESPONDER, Constants::STATUS_NO_AUTHN_CONTEXT);
    }
}
