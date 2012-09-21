<?php

/**
 * Ask the user for consent over all of the attributes being sent to the SP.
 *
 * Note this is part 1/2 of the Corto Consent Internal Response Processing service.
 */
class EngineBlock_Corto_Module_Service_ProvideConsent extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve($serviceName)
    {
        $response = $this->_server->getBindingsModule()->receiveResponse();
        $_SESSION['consent'][$response['_ID']]['response'] = $response;

        $attributes = EngineBlock_Corto_XmlToArray::attributes2array(
            $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
        );

        $serviceProviderEntityId = $attributes['urn:org:openconext:corto:internal:sp-entity-id'][0];
        unset($attributes['urn:org:openconext:corto:internal:sp-entity-id']);
        $spEntityMetadata = $this->_server->getRemoteEntity($serviceProviderEntityId);

        $identityProviderEntityId = $response['__']['OriginalIssuer'];
        $idpEntityMetadata = $this->_server->getRemoteEntity($identityProviderEntityId);

        $commonName = $attributes['urn:mace:dir:attribute-def:cn'][0];

        $consent = new EngineBlock_Corto_Model_Consent(
            $this->_server->getConfig('ConsentDbTable', 'consent'),
            $this->_server->getConfig('ConsentStoreValues', true),
            $response,
            $attributes
        );
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

        if (isset($spEntityMetadata['NoConsentRequired']) && $spEntityMetadata['NoConsentRequired']) {
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
}