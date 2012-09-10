<?php

class EngineBlock_Corto_Module_Service_ProcessConsent extends EngineBlock_Corto_Module_Service_Abstract
{
    public function serve()
    {
        if (!isset($_SESSION['consent'])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException('Session lost after consent');
        }
        if (!isset($_SESSION['consent'][$_POST['ID']]['response'])) {
            throw new EngineBlock_Corto_Module_Services_Exception("Stored response for ResponseID '{$_POST['ID']}' not found");
        }
        $response = $_SESSION['consent'][$_POST['ID']]['response'];

        $attributes = EngineBlock_Corto_XmlToArray::attributes2array(
            $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
        );
        $serviceProviderEntityId = $attributes['ServiceProvider'][0];
        unset($attributes['ServiceProvider']);

        if (!isset($_POST['consent']) || $_POST['consent'] !== 'yes') {
            $this->_server->redirect('/authentication/feedback/no-consent', 'No consent given...');
            return;
        }

        $this->_storeConsent($serviceProviderEntityId, $response, $attributes);

        $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:obtained';
        $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination'] = $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Return'];
        $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = 'INTERNAL';

        $this->_server->getBindingsModule()->send(
            $response,
            $this->_server->getRemoteEntity($serviceProviderEntityId)
        );
    }
}