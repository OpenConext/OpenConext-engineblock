<?php

use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;

class EngineBlock_Corto_Module_Service_ProcessConsent
    implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    const INTRODUCTION_EMAIL = 'introduction_email';

    /**
     * @var \EngineBlock_Corto_ProxyServer
     */
    protected $_server;

    /**
     * @var \EngineBlock_Corto_XmlToArray
     */
    protected $_xmlConverter;

    /**
     * @var EngineBlock_Corto_Model_Consent_Factory
     */
    private $_consentFactory;

    /**
     * @var EngineBlock_Mail_Mailer
     */
    private $_mailer;

    /**
     * @var EngineBlock_User_PreferredNameAttributeFilter
     */
    private $_preferredNameAttributeFilter;

    /**
     * @param EngineBlock_Corto_ProxyServer $server
     * @param EngineBlock_Corto_XmlToArray $xmlConverter
     * @param EngineBlock_Corto_Model_Consent_Factory $consentFactory
     * @param EngineBlock_Mail_Mailer $mailer
     * @param EngineBlock_User_PreferredNameAttributeFilter $preferredNameAttributeFilter
     */
    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        EngineBlock_Corto_Model_Consent_Factory $consentFactory,
        EngineBlock_Mail_Mailer $mailer,
        EngineBlock_User_PreferredNameAttributeFilter $preferredNameAttributeFilter
    ) {
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
        /** @var SAML2_Response|EngineBlock_Saml2_ResponseAnnotationDecorator $response */
        $response = $_SESSION['consent'][$_POST['ID']]['response'];

        $request = $this->_server->getReceivedRequestFromResponse($response);
        $serviceProvider = $this->_server->getRepository()->fetchServiceProviderByEntityId($request->getIssuer());

        $destinationMetadata = EngineBlock_SamlHelper::getDestinationSpMetadata(
            $serviceProvider,
            $request,
            $this->_server->getRepository()
        );

        if (!isset($_POST['consent']) || $_POST['consent'] !== 'yes') {
            throw new EngineBlock_Corto_Exception_NoConsentProvided('No consent given...');
        }

        $attributes = $response->getAssertion()->getAttributes();
        $consent = $this->_consentFactory->create($this->_server, $response, $attributes);
        $consent->storeConsent($destinationMetadata);
        if ($consent->countTotalConsent($response, $attributes) === 1) {
            $this->_sendIntroductionMail($response, $attributes);
        }

        $response->setConsent(SAML2_Const::CONSENT_OBTAINED);
        $response->setDestination($response->getReturn());
        $response->setDeliverByBinding('INTERNAL');

        $this->_server->getBindingsModule()->send(
            $response,
            $serviceProvider
        );
    }

    protected function _sendIntroductionMail(array $attributes)
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
