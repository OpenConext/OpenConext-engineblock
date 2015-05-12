<?php
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

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

        $request = $this->_server->getReceivedRequestFromResponse($response);
        $serviceProvider = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());
        $spMetadataChain = EngineBlock_SamlHelper::getSpRequesterChain(
            $serviceProvider,
            $request,
            $this->_server->getRepository()
        );

        $identityProviderEntityId = $response->getOriginalIssuer();
        $identityProvider = $this->_server->getRepository()->fetchIdentityProviderByEntityId($identityProviderEntityId);

        // Flush log if SP or IdP has additional logging enabled
        $requireAdditionalLogging = EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(
            array_merge($spMetadataChain, array($identityProvider))
        );
        if ($requireAdditionalLogging) {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $application->flushLog(
                'Activated additional logging for one or more SPs in the SP requester chain, or the IdP'
            );

            $log = $application->getLogInstance();
            $log->info('Raw HTTP request', array('http_request' => (string) $application->getHttpRequest()));
        }

        if ($this->isConsentDisabled($spMetadataChain, $identityProvider))   {
            $response->setConsent(SAML2_Const::CONSENT_INAPPLICABLE);

            $response->setDestination($response->getReturn());
            $response->setDeliverByBinding('INTERNAL');

            $this->_server->getBindingsModule()->send(
                $response,
                $serviceProvider
            );
            return;
        }

        $consentDestinationEntityMetadata = $spMetadataChain[0];

        $attributes = $response->getAssertion()->getAttributes();
        $consent = $this->_consentFactory->create($this->_server, $response, $attributes);
        $priorConsent = $consent->hasStoredConsent($consentDestinationEntityMetadata);
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
                'attributes'=> $attributes,
                'sp'        => $consentDestinationEntityMetadata,
                'idp'       => $identityProvider,
            ));
        $this->_server->sendOutput($html);
    }

    /**
     * @param ServiceProvider[] $serviceProviders
     * @param IdentityProvider $identityProvider
     * @return bool
     */
    private function isConsentDisabled(array $serviceProviders, IdentityProvider $identityProvider)
    {
        foreach ($serviceProviders as $serviceProvider) {
            if (!$serviceProvider->isConsentRequired) {
                return true;
            }

            if (in_array($serviceProvider->entityId, $identityProvider->spsEntityIdsWithoutConsent)) {
                return true;
            }
        }

        return false;
    }
}
