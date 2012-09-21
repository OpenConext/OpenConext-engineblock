<?php

class EngineBlock_Corto_Module_Service_ProcessConsent extends EngineBlock_Corto_Module_Service_Abstract
{
    const INTRODUCTION_EMAIL = 'introduction_email';

    public function serve($serviceName)
    {
        if (!isset($_SESSION['consent'])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException('Session lost after consent');
        }
        if (!isset($_SESSION['consent'][$_POST['ID']]['response'])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                "Stored response for ResponseID '{$_POST['ID']}' not found"
            );
        }
        $response = $_SESSION['consent'][$_POST['ID']]['response'];

        $attributes = EngineBlock_Corto_XmlToArray::attributes2array(
            $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
        );
        $serviceProviderEntityId = $attributes['urn:org:openconext:corto:internal:sp-entity-id'][0];
        unset($attributes['urn:org:openconext:corto:internal:sp-entity-id']);

        if (!isset($_POST['consent']) || $_POST['consent'] !== 'yes') {
            $this->_server->redirect('/authentication/feedback/no-consent', 'No consent given...');
            return;
        }

        $consent = new EngineBlock_Corto_Model_Consent(
            $this->_server->getConfig('ConsentDbTable', 'consent'),
            $this->_server->getConfig('ConsentStoreValues', true),
            $response,
            $attributes
        );
        $consent->storeConsent($serviceProviderEntityId, $this->_server->getRemoteEntity($serviceProviderEntityId));
        if ($consent->countTotalConsent($response, $attributes) === 1) {
            $this->_sendIntroductionMail($response, $attributes);
        }

        $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:obtained';
        $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination'] = $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Return'];
        $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = 'INTERNAL';

        $this->_server->getBindingsModule()->send(
            $response,
            $this->_server->getRemoteEntity($serviceProviderEntityId)
        );
    }

    protected function _sendIntroductionMail($response, $attributes)
    {
        if (!isset($attributes['urn:mace:dir:attribute-def:mail'])) {
            return;
        }
        $config = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        if (!isset($config->email->sendWelcomeMail) || !$config->email->sendWelcomeMail) {
            return;
        }

        $mailer = new EngineBlock_Mail_Mailer();
        $emailAddress = $attributes['urn:mace:dir:attribute-def:mail'][0];
        $mailer->sendMail(
            $emailAddress,
            EngineBlock_Corto_Module_Services::INTRODUCTION_EMAIL,
            array(
                '{user}' => $this->_getUserName($attributes)
            )
        );
    }

    protected function _getUserName($attributes)
    {
        if (isset($attributes['urn:mace:dir:attribute-def:givenName']) && isset($attributes['urn:mace:dir:attribute-def:sn'])) {
            return $attributes['urn:mace:dir:attribute-def:givenName'][0] . ' ' . $attributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:cn'])) {
            return $attributes['urn:mace:dir:attribute-def:cn'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:displayName'])) {
            return $attributes['urn:mace:dir:attribute-def:displayName'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:givenName'])) {
            return $attributes['urn:mace:dir:attribute-def:givenName'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:sn'])) {
            return $attributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:mail'])) {
            return $attributes['urn:mace:dir:attribute-def:mail'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:uid'])) {
            return $attributes['urn:mace:dir:attribute-def:uid'][0];
        }

        return "";
    }
}