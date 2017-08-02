<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\EngineBlockBundle\Exception\ResponseProcessingFailedException;

/**
 * The bindings module for Corto, which implements support for various data
 * bindings.
 */
class EngineBlock_Corto_Module_Bindings extends EngineBlock_Corto_Module_Abstract
{
    const KEY_REQUEST  = 'SAMLRequest';
    const KEY_RESPONSE = 'SAMLResponse';

    protected static $ASSERTION_SEQUENCE = array(
        'saml:Issuer',
        'ds:Signature',
        'saml:Subject',
        'saml:Conditions',
        'saml:Advice',
        'saml:Statement',
        'saml:AuthnStatement',
        'saml:AuhzDecisionStatement',
        'saml:AttributeStatement',
    );

    /**
     * Supported bindings in Corto.
     * @var array
     */
    protected $_bindings = array(
        'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'        => '_sendHTTPRedirect',
        'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'            => '_sendHTTPPost',
        'INTERNAL'                                                  => 'sendInternal',
        'JSON-Redirect'                                             => '_sendHTTPRedirect',
        'JSON-POST'                                                 => '_sendHTTPPost',
        null                                                        => '_sendHTTPRedirect'
    );

    /**
     * @var array
     */
    protected $_internalBindingMessages = array();

    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    protected $_server;

    /**
     * @var string
     */
    protected $_sspmodSamlMessageClassName;

    /**
     * @var OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration
     */
    private $_featureConfiguration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    public function __construct(EngineBlock_Corto_ProxyServer $server)
    {
        parent::__construct($server);

        $diContainer                       = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $this->_sspmodSamlMessageClassName = $diContainer->getMessageUtilClassName();
        $this->_featureConfiguration       = $diContainer->getFeatureConfiguration();
        $this->_logger                     = EngineBlock_ApplicationSingleton::getLog();
    }


    /**
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     * @throws EngineBlock_Corto_Module_Bindings_UnsupportedBindingException
     * @throws EngineBlock_Corto_Module_Bindings_VerificationException
     * @throws EngineBlock_Corto_Module_Bindings_Exception
     */
    public function receiveRequest()
    {
        // Detect the current binding from the super globals
        $sspBinding = SAML2_Binding::getCurrentBinding();

        // Receive the request.
        $sspRequest = $sspBinding->receive();

        if (!($sspRequest instanceof SAML2_AuthnRequest)) {
            throw new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException(
                'Unsupported Binding used',
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        if ($sspRequest->isMessageConstructedWithSignature()) {
            $log = $this->_server->getLogger();

            $log->notice(sprintf(
                'Received signed AuthnRequest from Issuer "%s" with signature method algorithm "%s"',
                $sspRequest->getIssuer(),
                $sspRequest->getSignatureMethod()
            ));
        }

        // check the IssueInstant against our own time to see if the SP's clock is getting out of sync
        $this->_checkIssueInstant( $sspRequest->getIssueInstant() );

        $ebRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($sspRequest);

        // Make sure the request from the sp has an Issuer
        $spEntityId = $ebRequest->getIssuer();
        if (!$spEntityId) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Missing <saml:Issuer> in message delivered to AssertionConsumerService.'
            );
        }
        // Remember sp for debugging
        $_SESSION['currentServiceProvider'] = $ebRequest->getIssuer();

        // Verify that we know this SP and have metadata for it.
        $serviceProvider = $this->_verifyKnownMessageIssuer(
            $spEntityId,
            $ebRequest->getDestination()
        );

        if (!$serviceProvider instanceof ServiceProvider) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Requesting entity '$spEntityId' is not a Service Provider"
            );
        }

        // Load the metadata for this IdP in SimpleSAMLphp style
        $sspSpMetadata = SimpleSAML_Configuration::loadFromArray(
            $this->mapCortoEntityMetadataToSspEntityMetadata($serviceProvider)
        );

        // Determine if we should check the signature of the message
        $wantRequestsSigned = (
            // If the destination wants the AuthnRequests signed
            $serviceProvider->requestsMustBeSigned
            ||
            // Or we currently demand that all AuthnRequests are sent signed
            $this->_server->getConfig('WantsAuthnRequestsSigned')
        );

        // If we should, then check it.
        if ($wantRequestsSigned) {
            // Check the Signature on the Request, if there is no signature, or verification fails
            // throw an exception.
            $className = $this->_sspmodSamlMessageClassName;
            if (!$className::checkSign($sspSpMetadata, $ebRequest->getSspMessage())) {
                throw new EngineBlock_Corto_Module_Bindings_VerificationException(
                    'Validation of received messages enabled, but no signature found on message.'
                );
            }
            /** @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator $ebRequest */
            $ebRequest->setWasSigned();
        }

        $this->_annotateRequestWithKeyId($ebRequest);

        return $ebRequest;
    }

    /**
     * @param $ebRequest
     */
    protected function _annotateRequestWithKeyId(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $ebRequest)
    {
        $keyId = $this->_server->getKeyId();

        if (!$keyId) {
            return;
        }

        $ebRequest->setKeyId($keyId);
    }

    /**
     * @return bool|EngineBlock_Saml2_ResponseAnnotationDecorator|SAML2_Response
     * @throws EngineBlock_Corto_Module_Bindings_UnsupportedBindingException
     * @throws EngineBlock_Corto_Module_Bindings_VerificationException
     * @throws EngineBlock_Corto_Module_Bindings_Exception
     */
    public function receiveResponse()
    {
        // First check if we parked a Response somewhere in memory and are just faking a SSO
        if ($sspResponse = $this->_receiveMessageFromInternalBinding(self::KEY_RESPONSE)) {
            // If so, no need to do any further verification, we trust our own responses.
            return $sspResponse;
        }

        // Compose the metadata for the 'SP' (which is us in this case) for use by SSP.
        $sspSpMetadata = $this->getSspOwnMetadata();

        // Detect the binding being used from the global variables (GET, POST, SERVER)
        $sspBinding = SAML2_Binding::getCurrentBinding();

        // Receive a message from the binding
        $sspResponse = $sspBinding->receive();
        if (!($sspResponse instanceof SAML2_Response)) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsupported Message received: ' . get_class($sspResponse),
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        // Log the response we received for troubleshooting
        $log = $this->_server->getLogger();
        $log->info(
            'Received response',
            array('saml_response' => $sspResponse->toUnsignedXML()->ownerDocument->saveXML())
        );

        // Make sure the response from the idp has an Issuer
        $idpEntityId = $sspResponse->getIssuer();
        if ($idpEntityId === NULL) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Missing <saml:Issuer> in message delivered to AssertionConsumerService.'
            );
        }

        // Remember idp for debugging
        $_SESSION['currentIdentityProvider'] = $idpEntityId;

        // We only support HTTP-POST binding for Responses
        if (!$sspBinding instanceof SAML2_HTTPPost) {
            throw new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException(
                'Unsupported Binding used: ' . get_class($sspBinding),
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        // Verify that we know this IdP and have metadata for it.
        $cortoIdpMetadata = $this->_verifyKnownMessageIssuer(
            $idpEntityId,
            $sspResponse->getDestination()
        );

        // Load the metadata for this IdP in SimpleSAMLphp style
        $sspIdpMetadata = SimpleSAML_Configuration::loadFromArray(
            $this->mapCortoEntityMetadataToSspEntityMetadata($cortoIdpMetadata)
        );

        // Make sure it has a InResponseTo (Unsolicited is not supported) but don't actually check that what it's
        // in response to is actually a message we sent quite yet.
        if (!$sspResponse->getInResponseTo()) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsolicited assertion (no InResponseTo in message) not supported!'
            );
        }

        // check the IssueInstant against our own time to see if the SP's clock is getting out of sync
        $this->_checkIssueInstant( $sspResponse->getIssueInstant() );

        try {
            // 'Process' the response, verify the signature, verify the timings.
            $className = $this->_sspmodSamlMessageClassName;

            if ($this->hasEncryptedAssertion($sspResponse)
                && !$this->_featureConfiguration->isEnabled('eb.encrypted_assertions')
            ) {
                $this->_logger->warning('Received encrypted assertion, the encrypted assertion feature is not enabled');

                throw new EngineBlock_Corto_Module_Bindings_Exception(
                    'Encrypted assertions are not supported',
                    EngineBlock_Exception::CODE_NOTICE
                );
            }

            if ($this->hasEncryptedAssertion($sspResponse)
                && $this->_featureConfiguration->isEnabled('eb.encrypted_assertions_require_outer_signature')
                && !$sspResponse->isMessageConstructedWithSignature()
            ) {
                $this->_logger->warning(
                    'Received encrypted assertion without outer signature, outer signature is required'
                );

                /** @see https://github.com/OpenConext/OpenConext-engineblock/issues/116 */
                throw new EngineBlock_Corto_Module_Bindings_Exception(
                    'Encrypted assertions are required to have an outer signature, but they have none',
                    EngineBlock_Exception::CODE_NOTICE
                );
            }

            try {
                $assertions = $className::processResponse($sspSpMetadata, $sspIdpMetadata, $sspResponse);
            } catch (sspmod_saml_Error $exception) {
                // Pass through, show specific feedback for responses with error status codes
                throw $exception;
            } catch (Exception $exception) {
                throw new ResponseProcessingFailedException(
                    sprintf('Response processing failed: %s', $exception->getMessage()), null, $exception
                );
            }

            // We only support 1 assertion
            if (count($assertions) > 1) {
                throw new EngineBlock_Corto_Module_Bindings_Exception(
                    'More than one assertion in received response.',
                    EngineBlock_Exception::CODE_NOTICE
                );
            }

            $sspResponse->setAssertions($assertions);
        }
        catch (ResponseProcessingFailedException $e) {
            // Passthrough, should be handled at a different level protecting against oracle attacks
            throw $e;
        }
        // This misnamed exception is only thrown when the Response status code is not Success
        catch (sspmod_saml_Error $e) {
            $log->notice(
                'Received an Error Response',
                array(
                    'exception_message' => $e->getMessage(),
                    'status'            => $e->getStatus(),
                    'substatus'         => $e->getSubStatus(),
                    'status_message'    => $e->getStatusMessage(),
                )
            );

            $status = $sspResponse->getStatus();

            $statusCodeDescription = $status['Code'];
            if (isset($status['SubCode'])) {
                $statusCodeDescription .= '/' . $status['SubCode'];
            }
            $statusCodeDescription = str_replace('urn:oasis:names:tc:SAML:2.0:status:', '', $statusCodeDescription);

            $statusMessage = !empty($status['Message']) ? $status['Message'] : '(No message provided)';

            // Throw the exception here instead of in the Corto Filters as Corto assumes the presence of an Assertion
            $exception = new EngineBlock_Corto_Exception_ReceivedErrorStatusCode(
                'Response received with Status: ' .
                $statusCodeDescription .
                ' - ' .
                $statusMessage
            );
            $exception->setFeedbackStatusCode($statusCodeDescription);
            $exception->setFeedbackStatusMessage($statusMessage);

            throw $exception;
        }
        // Thrown when timings are out of whack or other some such verification exceptions.
        catch (SimpleSAML_Error_Exception $e) {
            throw new EngineBlock_Corto_Module_Bindings_VerificationException(
                $e->getMessage(),
                EngineBlock_Exception::CODE_NOTICE,
                $e
            );
        }
        catch (Exception $e) {
            // Signature could not be verified by SSP
            if ($e->getMessage() === "Unable to validate Signature") {
                throw new EngineBlock_Corto_Module_Bindings_SignatureVerificationException(
                    $e->getMessage(),
                    EngineBlock_Exception::CODE_WARNING,
                    $e
                );
            }
            else {
                // General Response whackiness (like Destinations not matching)
                throw new EngineBlock_Corto_Module_Bindings_VerificationException(
                    $e->getMessage(),
                    EngineBlock_Exception::CODE_NOTICE,
                    $e
                );
            }

        }

        return new EngineBlock_Saml2_ResponseAnnotationDecorator($sspResponse);
    }

    protected function _receiveMessageFromInternalBinding($key)
    {
        if (!isset($this->_internalBindingMessages[$key])) {
            return false;
        }
        return $this->_internalBindingMessages[$key];
    }

    /**
     * Verify if a message has an issuer that is known to us. If not, it
     * throws a Corto_Module_Bindings_VerificationException.
     * @param string $messageIssuer
     * @param string $destination
     * @return AbstractRole Remote Entity that issued the message
     * @throws EngineBlock_Corto_Exception_UnknownIssuer
     */
    protected function _verifyKnownMessageIssuer($messageIssuer, $destination = '')
    {
        $remoteEntity = $this->_server->getRepository()->findEntityByEntityId($messageIssuer);

        if ($remoteEntity) {
            return $remoteEntity;
        }

        $this->_logger->notice(
            sprintf(
                'Tried to verify a message from issuer "%s", but there is no known entity with that ID.',
                $messageIssuer
            )
        );

        throw new EngineBlock_Corto_Exception_UnknownIssuer(
            "Issuer '{$messageIssuer}' is not a known remote entity? (please add SP/IdP to Remote Entities)",
            $messageIssuer,
            $destination
        );
    }

    /**
     * Check if the IssueInstant of the message is too far out of sync
     * @param integer $issueInstant
     */
    protected function _checkIssueInstant($issueInstant)
    {
        // check the IssueInstant against our own time to see if the SP's clock is getting out of sync
        // Ssp has a hard-coded limit of 60 seconds; use 30 here to catch an IdP's drifting clock early
        $time = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getTimeProvider()->time();
        $timeDelta = $time - $issueInstant;
        if (abs($timeDelta) > 30) {
            $this->_logger->notice(
                sprintf(
                    'IssueInstant of SAML message is off by %d seconds; might indicate (local or remote) clock synchronization issues',
                    $timeDelta
                )
            );
        }
    }

    public function send(
        EngineBlock_Saml2_MessageAnnotationDecorator $message,
        AbstractRole $remoteEntity
    ) {
        $bindingUrn = $message->getDeliverByBinding();
        $sspMessage = $message->getSspMessage();

        if ($bindingUrn === 'INTERNAL') {
            $this->sendInternal($message);
            return;
        }

        if ($this->shouldMessageBeSigned($sspMessage, $remoteEntity)) {
            $keyPair = $this->_server->getSigningCertificates();

            $sspMessage->setCertificates(array($keyPair->getCertificate()->toPem()));
            $sspMessage->setSignatureKey($keyPair->getPrivateKey()->toXmlSecurityKey());
        }

        $sspBinding = SAML2_Binding::getBinding($bindingUrn);
        if ($sspBinding instanceof SAML2_HTTPPost) {

            // SAML2int dictates that we MUST sign assertions.
            // The SAML2 library will do that for us, if we just set the key to sign with.
            if ($sspMessage instanceof SAML2_Response) {
                foreach ($sspMessage->getAssertions() as $assertion) {
                    $assertion->setCertificates($sspMessage->getCertificates());
                    $assertion->setSignatureKey($sspMessage->getSignatureKey());
                }
                // BWC dictates that we don't sign responses.
                $messageElement = $sspMessage->toUnsignedXML();
            }
            else {
                $messageElement = $sspMessage->toSignedXML();
            }

            $xml = $messageElement->ownerDocument->saveXML($messageElement);

            $this->validateXml($xml);

            $extra = '';
            $extra .= method_exists($message, 'getReturn')? '<input type="hidden" name="return" value="'     . htmlspecialchars($message->getReturn()) . '">' : '';
            $extra .= $sspMessage->getRelayState()           ? '<input type="hidden" name="RelayState" value="' . htmlspecialchars($sspMessage->getRelayState()) . '">' : '';

            $encodedMessage = htmlspecialchars(base64_encode($xml));

            $action = $sspMessage->getDestination();

            $log = $this->_server->getLogger();
            $log->info('HTTP-Post: Sending Message', array('saml_message' => $xml));

            $output = $this->_server->renderTemplate(
                'form',
                array(
                    'action' => $action,
                    'message' => $encodedMessage,
                    'xtra' => $extra,
                    'name' => $message->getMessageType(),
                    'trace' => $this->getTraceHtml($xml),
                )
            );
            $this->_server->sendOutput($output);

        } else if ($sspBinding instanceof SAML2_HTTPRedirect) {
            if ($sspMessage instanceof SAML2_Response) {
                throw new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException(
                    'May not send a Reponse via HTTP Redirect'
                );
            }

            $url = $sspBinding->getRedirectURL($sspMessage);
            $this->_server->redirect($url, $message);
        }
        else {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsupported Binding'
            );
        }
    }

    protected function validateXml($xml)
    {
        $schemaUrl = 'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-protocol-2.0.xsd';
        if ($this->_server->getConfig('debug') && ini_get('allow_url_fopen') && file_exists($schemaUrl)) {
            $dom = SAML2_DOMDocumentFactory::fromString($xml);
            if (!$dom->schemaValidate($schemaUrl)) {
                throw new Exception('Message XML doesnt validate against XSD at Oasis-open.org?!');
            }
        }
    }

    protected function shouldMessageBeSigned(SAML2_Message $sspMessage, AbstractRole $remoteEntity)
    {
        if ($sspMessage instanceof SAML2_Response) {
            return true;
        }

        if (!$sspMessage instanceof SAML2_AuthnRequest) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsupported Message type: ' . get_class($sspMessage)
            );
        }

        // Determine if we should sign the message
        return $remoteEntity->requestsMustBeSigned || $this->_server->getConfig('WantsAuthnRequestsSigned');
    }

    /**
     * See if the given binding is supported by EngineBlock
     *
     * @param string $binding
     * @return bool
     */
    public function isSupportedBinding($binding)
    {
        return (isset($this->_bindings[$binding]));
    }

    public function sendInternal(EngineBlock_Saml2_MessageAnnotationDecorator $message)
    {
        // Store the message
        $name = $message->getMessageType();
        $this->_internalBindingMessages[$name] = $message;

        /** @var SAML2_Message $message */
        $destinationLocation = $message->getDestination();
        $parameters = $this->_server->getParametersFromUrl($destinationLocation);
        if (isset($parameters['RemoteIdPMd5'])) {
            $this->_server->setRemoteIdpMd5($parameters['RemoteIdPMd5']);
        }

        $log = $this->_server->getLogger();
        $log->info("Using internal binding for destination $destinationLocation", array('url_params' => $parameters));

        $serviceName = $parameters['ServiceName'];

        $log->info("Calling service '$serviceName'");
        $this->_server->getServicesModule()->serve($serviceName);
        $log->info("Done calling service '$serviceName'");
    }

    /**
     * @param $key
     * @param $message
     * @return $this
     */
    public function registerInternalBindingMessage($key, $message)
    {
        $this->_internalBindingMessages[$key] = $message;
        return $this;
    }

    /**
     * @param ServiceProvider $cortoEntityMetadata
     * @return array
     */
    protected function mapCortoEntityMetadataToSspEntityMetadata(AbstractRole $cortoEntityMetadata)
    {
        /** @var EngineBlock_X509_Certificate[] $certificates */
        $certificates = $cortoEntityMetadata->certificates;

        $config = array(
            'entityid'            => $cortoEntityMetadata->entityId,
            'keys'                => array(),
        );
        if ($cortoEntityMetadata instanceof IdentityProvider) {
            $config['SingleSignOnService'] = $cortoEntityMetadata->singleSignOnServices[0]->location;
        }
        if ($cortoEntityMetadata instanceof ServiceProvider) {
            $config['AssertionConsumerService'] = $cortoEntityMetadata->assertionConsumerServices[0]->location;
        }
        foreach ($certificates as $certificate) {
            $config['keys'][] = array(
                'signing'         => true,
                'type'            => 'X509Certificate',
                'X509Certificate' => $certificate->toCertData(),
            );
        }
        return $config;
    }

    /**
     * @return SimpleSAML_Configuration
     */
    protected function getSspOwnMetadata()
    {
        $keyPair = $this->_server->getSigningCertificates();

        $spMetadata = SimpleSAML_Configuration::loadFromArray(
            array(
                'entityid'            => $this->_server->getUrl('spMetadataService'),
                'SingleSignOnService' => array(
                    array(
                        'Binding'  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                        'Location' => $this->_server->getUrl('spMetadataService'),
                    ),
                ),
                'keys'                => array(
                    array(
                        'signing'         => true,
                        'type'            => 'X509Certificate',
                        'X509Certificate' => $keyPair->getCertificate()->toCertData(),
                    ),
                    array(
                        'signing'         => true,
                        'type'            => 'X509Certificate',
                        'X509Certificate' => $keyPair->getCertificate()->toCertData(),
                    ),
                ),
                'privatekey' => $keyPair->getPrivateKey() ? $keyPair->getPrivateKey()->filePath() : '',
            )
        );
        return $spMetadata;
    }

    /**
     * Determines if a Response carries an encrypted assertion.
     *
     * @param SAML2_Response $sspResponse
     * @return bool
     */
    private function hasEncryptedAssertion(SAML2_Response $sspResponse)
    {
        $hasEncryptedAssertion = false;
        foreach ($sspResponse->getAssertions() as $assertion) {
            if ($assertion instanceof SAML2_EncryptedAssertion) {
                $hasEncryptedAssertion = true;
                break;
            }
        }
        return $hasEncryptedAssertion;
    }

    private function getTraceHtml($xml)
    {
        if (!$this->_server->getConfig('debug', false)) {
            return '';
        }

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xml);
        $xml = $doc->saveXML();

        return htmlentities(trim($xml));
    }
}
