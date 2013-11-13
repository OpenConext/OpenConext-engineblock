<?php

class EngineBlock_Corto_Module_Service_ProcessConsent
    implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    const INTRODUCTION_EMAIL = 'introduction_email';

    /** @var \EngineBlock_Corto_ProxyServer */
    protected $_server;

    /** @var EngineBlock_Corto_XmlToArray */
    protected $_xmlConverter;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private $_consentFactory;

    /** @var EngineBlock_Mail_Mailer */
    private $_mailer;

    /** @var EngineBlock_User_PreferredNameAttributeFilter */
    private $_preferredNameAttributeFilter;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        EngineBlock_Corto_Model_Consent_Factory $consentFactory,
        EngineBlock_Mail_Mailer $mailer,
        EngineBlock_User_PreferredNameAttributeFilter $preferredNameAttributeFilter
    )
    {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_consentFactory = $consentFactory;
        $this->_mailer = $mailer;
        $this->_preferredNameAttributeFilter = $preferredNameAttributeFilter;
    }

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

        $attributes = $this->_xmlConverter->attributesToArray(
            $response['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute']
        );
        $serviceProviderEntityId = $attributes['urn:org:openconext:corto:internal:sp-entity-id'][0];
        unset($attributes['urn:org:openconext:corto:internal:sp-entity-id']);

        if (!isset($_POST['consent']) || $_POST['consent'] !== 'yes') {
            throw new EngineBlock_Corto_Exception_NoConsentProvided('No consent given...');
        }

        $consent = $this->_consentFactory->create($this->_server, $response, $attributes);
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

        $emailAddress = $attributes['urn:mace:dir:attribute-def:mail'][0];
        $this->_mailer->sendMail(
            $emailAddress,
            EngineBlock_Corto_Module_Services::INTRODUCTION_EMAIL,
            array(
                '{user}' => $this->_preferredNameAttributeFilter->getAttribute($attributes)
            )
        );
    }
}