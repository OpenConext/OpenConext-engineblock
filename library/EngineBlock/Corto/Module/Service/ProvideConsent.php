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

        $attributes = $response->getAssertion()->getAttributes();

        $serviceProviderEntityId = $attributes['urn:org:openconext:corto:internal:sp-entity-id'][0];

        unset($attributes['urn:org:openconext:corto:internal:sp-entity-id']);
        $spEntityMetadata = $this->_server->getRemoteEntity($serviceProviderEntityId);

        $identityProviderEntityId = $response->getOriginalIssuer();
        $idpEntityMetadata = $this->_server->getRemoteEntity($identityProviderEntityId);

        // Flush log if SP or IdP has additional logging enabled
        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging($spEntityMetadata, $idpEntityMetadata)) {
            EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->flushQueue();
        }

        $commonName = $attributes['urn:mace:dir:attribute-def:cn'][0];

        if ($this->isConsentDisabled($spEntityMetadata, $idpEntityMetadata, $serviceProviderEntityId))   {
            $response->setConsent(SAML2_Const::CONSENT_INAPPLICABLE);

            $response->setDestination($response->getReturn());
            $response->setDeliverByBinding('INTERNAL');

            $this->_server->getBindingsModule()->send(
                $response,
                $spEntityMetadata
            );
            return;
        }

        $consent = $this->_consentFactory->create($this->_server, $response, $attributes);
        $priorConsent = $consent->hasStoredConsent($serviceProviderEntityId, $spEntityMetadata);
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
        return;

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
