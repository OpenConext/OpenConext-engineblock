<?php

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
        $spEntityMetadata = $this->_server->getRemoteEntity($request->getIssuer());
        $spMetadataChain = EngineBlock_SamlHelper::getSpRequesterChain($spEntityMetadata, $request, $this->_server);

        $identityProviderEntityId = $response->getOriginalIssuer();
        $idpEntityMetadata = $this->_server->getRemoteEntity($identityProviderEntityId);

        // Flush log if SP or IdP has additional logging enabled
        $requireAdditionalLogging = EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(
            array_merge($spMetadataChain, array($idpEntityMetadata))
        );
        if (
            $this->_server->getConfig('debug', false) ||
            $requireAdditionalLogging
        ) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        if ($this->isConsentDisabled($spMetadataChain, $idpEntityMetadata))   {
            $response->setConsent(SAML2_Const::CONSENT_INAPPLICABLE);

            $response->setDestination($response->getReturn());
            $response->setDeliverByBinding('INTERNAL');

            $this->_server->getBindingsModule()->send(
                $response,
                $spEntityMetadata
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
                $spEntityMetadata
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
                'idp'       => $idpEntityMetadata,
            ));
        $this->_server->sendOutput($html);
    }

    /**
     * @param array[] $spEntities
     * @param array $idpEntityMetadata
     * @return bool
     */
    private function isConsentDisabled(array $spEntities, array $idpEntityMetadata)
    {
        foreach ($spEntities as $spEntityMetadata) {
            if ($this->isConsentGloballyDisabled($spEntityMetadata)) {
                return true;
            }

            if ($this->isConsentDisabledByIdpForCurrentSp($idpEntityMetadata, $spEntityMetadata['EntityID'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $spEntityMetadata
     * @return bool
     */
    private function isConsentGloballyDisabled(array $spEntityMetadata)
    {
        return isset($spEntityMetadata['NoConsentRequired'])
            && $spEntityMetadata['NoConsentRequired'];
    }

    /**
     * @param array $idpEntityMetadata
     * @param $serviceProviderEntityId
     * @return bool
     */
    private function isConsentDisabledByIdpForCurrentSp(array $idpEntityMetadata, $serviceProviderEntityId)
    {
        if (isset($idpEntityMetadata['SpsWithoutConsent'])
            && in_array($serviceProviderEntityId, $idpEntityMetadata['SpsWithoutConsent'])) {
            return true;
        }

        return false;
    }
}
