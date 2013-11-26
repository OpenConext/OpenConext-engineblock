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

    /** @var EngineBlock_Corto_Filter_Command_AttributeReleasePolicy */
    private $_arpFilter;

    /** @var EngineBlock_Corto_Model_Consent_Repository */
    private $_consentRepository;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        EngineBlock_Corto_Model_Consent_Factory $consentFactory,
        EngineBlock_Mail_Mailer $mailer,
        EngineBlock_User_PreferredNameAttributeFilter $preferredNameAttributeFilter,
        EngineBlock_Corto_Filter_Command_AttributeReleasePolicy $arpFilter,
        EngineBlock_Corto_Model_Consent_Repository $consentRepository
    )
    {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_consentFactory = $consentFactory;
        $this->_mailer = $mailer;
        $this->_preferredNameAttributeFilter = $preferredNameAttributeFilter;
        $this->_arpFilter = $arpFilter;
        $this->_consentRepository = $consentRepository;
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

        $spEntityMetadata = $this->_server->getRemoteEntity($serviceProviderEntityId);

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
        $this->_consentRepository->store($consent);
        if ($this->_consentRepository->countTotalConsent($consent->getUserIdHash()) === 1) {
            $this->_sendIntroductionMail($attributes);
        }

        $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Consent'] = 'urn:oasis:names:tc:SAML:2.0:consent:obtained';
        $response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Destination'] = $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Return'];
        $response[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = 'INTERNAL';

        $this->_server->getBindingsModule()->send(
            $response,
            $this->_server->getRemoteEntity($serviceProviderEntityId)
        );
    }

    protected function _sendIntroductionMail($attributes)
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