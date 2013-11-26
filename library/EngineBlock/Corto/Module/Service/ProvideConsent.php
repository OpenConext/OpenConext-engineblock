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

    /** @var EngineBlock_Corto_Filter_Command_AttributeReleasePolicy */
    private $_arpFilter;

    /** @var EngineBlock_Corto_Model_Consent_Repository */
    private $_consentRepository;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        EngineBlock_Corto_Model_Consent_Factory $consentFactory,
        EngineBlock_Corto_Filter_Command_AttributeReleasePolicy $arpFilter,
        EngineBlock_Corto_Model_Consent_Repository $consentRepository
    )
    {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_consentFactory = $consentFactory;
        $this->_arpFilter = $arpFilter;
        $this->_consentRepository = $consentRepository;
    }

    public function serve($serviceName)
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $_SESSION['consent'][$response['_ID']]['response'] = $response;

        $attributes = $this->_xmlConverter->attributesToArray($response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']);

        $serviceProviderEntityId = $attributes['urn:org:openconext:corto:internal:sp-entity-id'][0];

        unset($attributes['urn:org:openconext:corto:internal:sp-entity-id']);
        $spEntityMetadata = $this->_server->getRemoteEntity($serviceProviderEntityId);

        $identityProviderEntityId = $response['__']['OriginalIssuer'];
        $idpEntityMetadata = $this->_server->getRemoteEntity($identityProviderEntityId);

        // Flush log if SP or IdP has additional logging enabled
        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging($spEntityMetadata, $idpEntityMetadata)) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        $commonName = $attributes['urn:mace:dir:attribute-def:cn'][0];

        if ($this->isConsentDisabled($spEntityMetadata, $idpEntityMetadata, $serviceProviderEntityId))   {
            $response['_Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:inapplicable';

            $response['_Destination'] = $response['__']['Return'];
            $response['__']['ProtocolBinding'] = 'INTERNAL';

            $this->_server->getBindingsModule()->send(
                $response,
                $spEntityMetadata
            );
            return;
        }

        // Should be moved to generic method
        // Filter attributes
        $this->_arpFilter->setSpMetadata($spEntityMetadata);
        $this->_arpFilter->setResponseAttributes($attributes);
        $this->_arpFilter->execute();
        $filteredResponseAttributes = $this->_arpFilter->getResponseAttributes();

        $userId = EngineBlock_Corto_Model_Consent_Factory::extractUidFromResponse($response);
        $consent = $this->_consentFactory->create(
            $this->_server,
            $userId,
            $serviceProviderEntityId,
            $filteredResponseAttributes
        );
        if ($this->_consentRepository->isStored($consent)) {
            $this->_consentRepository->updateUsage($consent);
            $response['_Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:prior';

            $response['_Destination'] = $response['__']['Return'];
            $response['__']['ProtocolBinding'] = 'INTERNAL';

            $this->_server->getBindingsModule()->send(
                $response,
                $spEntityMetadata
            );
            return;
        }
        /*
         * We don't show the nameId for now as we must base this on the nameID format and this will
         * be later on in the process defined.
         */
        $nameId = null;

        $html = $this->_server->renderTemplate(
            'consent',
            array(
                'action'    => $this->_server->getUrl('processConsentService'),
                'ID'        => $response['_ID'],
                'attributes'=> $filteredResponseAttributes,
                'sp'        => $spEntityMetadata,
                'idp'       => $idpEntityMetadata,
                'commonName'=> $commonName,
                'nameId'    => $nameId,
            ));
        $this->_server->sendOutput($html);
    }

    /**
     * @param array $spEntityMetadata
     * @param array $idpEntityMetadata
     * @param $serviceProviderEntityId
     * @return bool
     */
    private function isConsentDisabled(array $spEntityMetadata, array $idpEntityMetadata, $serviceProviderEntityId)
    {
        if ($this->isConsentGloballyDisabled($spEntityMetadata)
            || $this->isConsentDisabledByIdpForCurrentSp($idpEntityMetadata, $serviceProviderEntityId) ) {
            return true;
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
