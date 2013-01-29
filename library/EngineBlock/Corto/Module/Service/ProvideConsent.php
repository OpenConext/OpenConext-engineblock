<?php

/**
 * Ask the user for consent over all of the attributes being sent to the SP.
 *
 * Note this is part 1/2 of the Corto Consent Internal Response Processing service.
 */
class EngineBlock_Corto_Module_Service_ProvideConsent extends EngineBlock_Corto_Module_Service_Abstract
{
    /**
     * @var EngineBlock_Corto_Model_Consent_Factory
     * @workaround made these vars public to access them from unit test
     */
    public $consentFactory;

    protected function init() {
        // @todo inject/set consent factory instead of getting it directly from di container
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $this->consentFactory = $diContainer[EngineBlock_Application_DiContainer::CONSENT_FACTORY];
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

        $commonName = $attributes['urn:mace:dir:attribute-def:cn'][0];

        $consent = $this->consentFactory->create($this->_server, $response, $attributes);
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

        if ($this->isConsentDisabled($spEntityMetadata, $identityProviderEntityId))   {
            $response['_Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:inapplicable';

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
                'attributes'=> $attributes,
                'sp'        => $spEntityMetadata,
                'idp'       => $idpEntityMetadata,
                'commonName'=> $commonName,
            ));
        $this->_server->sendOutput($html);
    }

    /**
     * @param array $spEntityMetadata
     * @param string $identityProviderEntityId
     * @return bool
     */
    private function isConsentDisabled(array $spEntityMetadata, $identityProviderEntityId)
    {
        if ($this->isConsentGloballyDisabled($spEntityMetadata)
            || $this->isConsentDisabledForCurrentIdp($spEntityMetadata, $identityProviderEntityId) ) {
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
     * @param array $spEntityMetadata
     * @param string $identityProviderEntityId
     * @return bool
     */
    private function isConsentDisabledForCurrentIdp(array $spEntityMetadata, $identityProviderEntityId)
    {
        if (isset($spEntityMetadata['IdPsWithoutConsent'])
            && in_array($identityProviderEntityId, $spEntityMetadata['IdPsWithoutConsent'])) {
            return true;
        }

        return false;
    }
}