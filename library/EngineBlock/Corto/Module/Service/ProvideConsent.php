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

        $consent = $this->_consentFactory->create($this->_server, $response, $attributes);
        $priorConsent = $consent->hasStoredConsent($serviceProviderEntityId, $spEntityMetadata);
        if ($priorConsent) {
            $response['_Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:prior';

            $response['_Destination'] = $response['__']['Return'];
            $response['__']['ProtocolBinding'] = 'INTERNAL';

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
                'ID'        => $response['_ID'],
                'attributes'=> $consent->getFilteredResponseAttributes(),
                'sp'        => $spEntityMetadata,
                'idp'       => $idpEntityMetadata,
                'commonName'=> $commonName,
                'nameId'    => $this->resolveNameId($response),
            ));
        $this->_server->sendOutput($html);
    }

    /*
     * https://wiki.surfnet.nl/display/conextdocumentation/Consent+screen+improvements
     */
    private function resolveNameId($response)
    {
        /*
         * We have problem here namely the format NameID is not 'calculated' yet. This is done in the EngineBlock_Corto_Filter_Command_SetNameId.
         *
         * Complex refactoring is required, but not for now. Decided is to - for now - not show the nameId on the consent screen. If the setNameId is done
         * before consent we can uncomment the following code.
         */

        $nameId = null;
//        if (isset($response['saml:Assertion']['saml:Subject']['saml:NameID']['_Format'])) {
//            $format = $response['saml:Assertion']['saml:Subject']['saml:NameID']['_Format'];
//            switch ($format) {
//                case EngineBlock_Urn::SAML1_1_NAMEID_FORMAT_UNSPECIFIED:
//                case EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_PERSISTENT:
//                case EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_UNSPECIFIED:
//                    if (isset($response['saml:Assertion']['saml:Subject']['saml:NameID']['__v'])) {
//                        $nameId = $response['saml:Assertion']['saml:Subject']['saml:NameID']['__v'];
//                    }
//            }
//        }
        return $nameId;

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