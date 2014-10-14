<?php
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;

/**
 * Ask the user for consent over all of the attributes being sent to the SP.
 *
 * Note this is part 1/2 of the Corto Consent Internal Response Processing service.
 */
class EngineBlock_Corto_Module_Service_ProvideConsent
    implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /** @var \EngineBlock_Corto_ProxyServer */
    private $_server;
    /**
     * @var EngineBlock_Corto_XmlToArray
     */
    private $_xmlConverter;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private  $_consentFactory;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        EngineBlock_Corto_Model_Consent_Factory $consentFactory
    )
    {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_consentFactory = $consentFactory;
    }

    public function serve($serviceName)
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $_SESSION['consent'][$response->getId()]['response'] = $response;

        $attributes = $response->getAssertion()->getAttributes();

        $serviceProviderEntityId = $attributes['urn:org:openconext:corto:internal:sp-entity-id'][0];

        unset($attributes['urn:org:openconext:corto:internal:sp-entity-id']);
        $serviceProvider = $this->_server->getRepository()->fetchServiceProviderByEntityId($serviceProviderEntityId);

        $identityProviderEntityId = $response->getOriginalIssuer();
        $identityProvider = $this->_server->getRepository()->fetchIdentityProviderByEntityId($identityProviderEntityId);

        // Flush log if SP or IdP has additional logging enabled
        if (
            $this->_server->getConfig('debug', false) ||
            EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging($serviceProvider, $identityProvider)
        ) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        if ($this->isConsentDisabled($serviceProvider, $identityProvider, $serviceProviderEntityId))   {
            $response->setConsent(SAML2_Const::CONSENT_INAPPLICABLE);

            $response->setDestination($response->getReturn());
            $response->setDeliverByBinding('INTERNAL');

            $this->_server->getBindingsModule()->send(
                $response,
                $serviceProvider
            );
            return;
        }

        $consent = $this->_consentFactory->create($this->_server, $response, $attributes);
        $priorConsent = $consent->hasStoredConsent($serviceProviderEntityId, $serviceProvider);
        if ($priorConsent) {
            $response->setConsent(SAML2_Const::CONSENT_PRIOR);

            $response->setDestination($response->getReturn());
            $response->setDeliverByBinding('INTERNAL');

            $this->_server->getBindingsModule()->send(
                $response,
                $serviceProvider
            );
            return;
        }

        $html = $this->_server->renderTemplate(
            'consent',
            array(
                'action'    => $this->_server->getUrl('processConsentService'),
                'ID'        => $response->getId(),
                'attributes'=> $consent->getFilteredResponseAttributes(),
                'sp'        => $serviceProvider,
                'idp'       => $identityProvider,
            ));
        $this->_server->sendOutput($html);
    }

    /**
     * @param ServiceProviderEntity  $serviceProvider
     * @param IdentityProviderEntity $identityProvider
     * @return bool
     */
    private function isConsentDisabled(ServiceProviderEntity $serviceProvider, IdentityProviderEntity $identityProvider)
    {
        if ($serviceProvider->isConsentRequired) {
            return true;
        }

        if (in_array($serviceProvider->entityId, $identityProvider->spsEntityIdsWithoutConsent)) {
            return true;
        }

        return false;
    }
}
