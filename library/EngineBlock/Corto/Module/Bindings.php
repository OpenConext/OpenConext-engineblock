<?php

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Validator\AllowedSchemeValidator;
use OpenConext\EngineBlockBundle\Exception\ResponseProcessingFailedException;
use SAML2\AuthnRequest;
use SAML2\Binding;
use SAML2\Certificate\KeyLoader;
use SAML2\Configuration\Destination;
use SAML2\Configuration\IdentityProvider as Saml2IdentityProvider;
use SAML2\Configuration\PrivateKey;
use SAML2\Configuration\ServiceProvider as Saml2ServiceProvider;
use SAML2\DOMDocumentFactory;
use SAML2\EncryptedAssertion;
use SAML2\Response\Exception\PreconditionNotMetException;
use SAML2\HTTPPost;
use SAML2\HTTPRedirect;
use SAML2\Message;
use SAML2\Response;
use SAML2\Signature\PublicKeyValidator;

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
     * @var OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration
     */
    private $_featureConfiguration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var AllowedSchemeValidator
     */
    private $acsLocationSchemeValidator;

    public function __construct(EngineBlock_Corto_ProxyServer $server)
    {
        parent::__construct($server);

        $diContainer                       = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $this->_featureConfiguration = $diContainer->getFeatureConfiguration();
        $this->_logger = EngineBlock_ApplicationSingleton::getLog();
        $this->twig = $diContainer->getTwigEnvironment();
        $this->acsLocationSchemeValidator = $diContainer->getAcsLocationSchemeValidator();
    }

    /**
     * Process an authorization request.
     *
     * NOTE: Never call this method more than once for the same request. This method
     *       does not cache its results.
     *
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     * @throws EngineBlock_Corto_Module_Bindings_UnsupportedBindingException
     * @throws EngineBlock_Corto_Module_Bindings_VerificationException
     * @throws EngineBlock_Corto_Module_Bindings_Exception
     */
    public function receiveRequest()
    {
        // Detect the current binding from the super globals
        $sspBinding = Binding::getCurrentBinding();

        // Receive the request.
        $sspRequest = $sspBinding->receive();

        if (!($sspRequest instanceof AuthnRequest)) {
            throw new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException(
                'Unsupported Binding used',
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        $signatureMethod = $this->getRequestSignatureMethod($sspRequest);

        if ($signatureMethod !== null) {
            $log = $this->_server->getLogger();

            $log->notice(sprintf(
                'Received signed AuthnRequest from Issuer "%s" with signature method algorithm "%s"',
                $sspRequest->getIssuer(),
                $signatureMethod
            ));

            $forbiddenSignatureMethods = $this->_server->getConfig('forbiddenSignatureMethods');
            if (in_array($signatureMethod, $forbiddenSignatureMethods)) {
                throw new EngineBlock_Corto_Module_Bindings_UnsupportedSignatureMethodException($signatureMethod);
            }
        }

        // Test if there is an invalid ACS location uri scheme in use
        $acsLocation = $sspRequest->getAssertionConsumerServiceURL();
        $this->assertValidAcsLocationScheme($acsLocation);

        // check the IssueInstant against our own time to see if the SP's clock is getting out of sync
        $this->_checkIssueInstant( $sspRequest->getIssueInstant(), 'SP',  $sspRequest->getIssuer() );

        $ebRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($sspRequest);

        // Make sure the request from the sp has an Issuer
        $spEntityId = $ebRequest->getIssuer();
        if ($spEntityId === null) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Missing <saml:Issuer> in message delivered to AssertionConsumerService.'
            );
        }
        // Remember sp for debugging
        $_SESSION['currentServiceProvider'] = $ebRequest->getIssuer();

        // Verify that we know this SP and have metadata for it.
        $serviceProvider = $this->_verifyKnownSP(
            $spEntityId,
            $ebRequest->getDestination()
        );

        if (!$serviceProvider instanceof ServiceProvider) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Requesting entity '$spEntityId' is not a Service Provider"
            );
        }

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
            $signatureVerifier = new PublicKeyValidator(
                $this->_logger,
                new KeyLoader()
            );

            $saml2Config = $this->mapEngineBlockSpToSaml2Sp($serviceProvider);
            $saml2Message = $ebRequest->getSspMessage();

            if (!$signatureVerifier->canValidate($saml2Message, $saml2Config)) {
                throw new EngineBlock_Corto_Module_Bindings_VerificationException(
                    'Validation of received messages enabled, but no keys are configured for service provider.'
                );
            } elseif (!$signatureVerifier->hasValidSignature($saml2Message, $saml2Config)) {
                // Exceptions are thrown for specific validation errors.
                throw new EngineBlock_Corto_Module_Bindings_VerificationException(
                    'Validation of received messages enabled, but no signature found on message.'
                );
            }

            /** @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator $ebRequest */
            $ebRequest->setWasSigned();
        }

        $this->_annotateRequestWithKeyId($ebRequest);

        // Corto service modules (SingleSignOn) can use the received
        // request object without having to process it again.
        $this->_server->setReceivedRequest($ebRequest);

        return $ebRequest;
    }

    /**
     * @param Message $message
     * @return null|string
     */
    private function getRequestSignatureMethod(Message $message)
    {
        if ($message->isMessageConstructedWithSignature()) {
            return $message->getSignatureMethod();
        }

        if (isset($_GET['SigAlg'])) {
            return $_GET['SigAlg'];
        }
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
     * @return bool|EngineBlock_Saml2_ResponseAnnotationDecorator|Response
     *
     * @throws EngineBlock_Corto_Exception_ReceivedErrorStatusCode
     * @throws EngineBlock_Corto_Module_Bindings_Exception
     * @throws EngineBlock_Corto_Module_Bindings_SignatureVerificationException
     * @throws EngineBlock_Corto_Module_Bindings_UnsupportedBindingException
     * @throws EngineBlock_Corto_Module_Bindings_VerificationException
     */
    public function receiveResponse()
    {
        // First check if we parked a Response somewhere in memory and are just faking a SSO
        if ($sspResponse = $this->_receiveMessageFromInternalBinding(self::KEY_RESPONSE)) {
            // If so, no need to do any further verification, we trust our own responses.
            return $sspResponse;
        }

        // Detect the binding being used from the global variables (GET, POST, SERVER)
        $sspBinding = Binding::getCurrentBinding();

        // Receive a message from the binding
        $sspResponse = $sspBinding->receive();
        if (!($sspResponse instanceof Response)) {
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
        if (!$sspBinding instanceof HTTPPost) {
            throw new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException(
                'Unsupported Binding used: ' . get_class($sspBinding),
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        // Verify that we know this IdP and have metadata for it.
        $cortoIdpMetadata = $this->_verifyKnownIdP(
            $idpEntityId,
            $sspResponse->getDestination()
        );

        // Make sure it has a InResponseTo (Unsolicited is not supported) but don't actually check that what it's
        // in response to is actually a message we sent quite yet.
        if ($sspResponse->getInResponseTo() === null) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsolicited assertion (no InResponseTo in message) not supported!'
            );
        }

        // check the IssueInstant against our own time to see if the SP's clock is getting out of sync
        $this->_checkIssueInstant( $sspResponse->getIssueInstant(), 'IdP', $idpEntityId );

        try {
            // 'Process' the response, verify the signature, verify the timings.
            if ($this->hasEncryptedAssertion($sspResponse)
                && !$this->_featureConfiguration->isEnabled('eb.encrypted_assertions')
            ) {
                $this->_logger->warning('Received encrypted assertion, the encrypted assertion feature is not enabled');

                throw new EngineBlock_Corto_Module_Bindings_Exception(
                    'Encrypted assertions are not supported',
                    EngineBlock_Exception::CODE_NOTICE
                );
            }

            if ($this->hasEncryptedAssertion($sspResponse) && !$sspResponse->isMessageConstructedWithSignature()) {
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
                $expectedDestination = $this->_server->getUrl('assertionConsumerService');
                if ($sspResponse->getDestination() === null) {
                    // SAML2 requires a destination, while EngineBlock allows
                    // messages without Destination element.
                    $sspResponse->setDestination($expectedDestination);
                }

                // We don't actually require IDPs to encrypt their assertions, but if the
                // feature is enabled in EB, and an encrypted assertion is received,
                // we require the SAML2 library to decrypt it.
                $requireEncryption = $this->hasEncryptedAssertion($sspResponse);

                $processor = new Response\Processor($this->_logger);
                $assertions = $processor->process(
                    $this->getSaml2OwnMetadata($requireEncryption),
                    $this->mapEngineBlockIdpToSaml2Idp($cortoIdpMetadata, $requireEncryption),
                    new Destination($expectedDestination),
                    $sspResponse
                );
            } catch (PreconditionNotMetException $exception) {
                // Pass through, show specific feedback for responses with error status codes
                // SAML2 throws a 'precondition not met' exception if the response
                // was unsuccessful. The specific 'no success response' case
                // should be handled here, so we can show specific information to
                // the user.
                if ($sspResponse->isSuccess()) {
                    throw new ResponseProcessingFailedException(
                        sprintf('Response processing failed: %s', $exception->getMessage()), null, $exception
                    );
                }

                $status = $sspResponse->getStatus();

                $log->notice(
                    'Received an Error Response',
                    array(
                        'exception_message' => $exception->getMessage(),
                        'status'            => $status['Code'],
                        'substatus'         => $status['SubCode'],
                        'status_message'    => $status['Message'],
                    )
                );

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

            $sspResponse->setAssertions([
                $assertions->getOnlyElement()
            ]);
        }
        catch (ResponseProcessingFailedException $e) {
            // Passthrough, should be handled at a different level protecting against oracle attacks
            throw $e;
        }
        catch (EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e) {
            // This exception is generated above, and should be shown as a distinct error to the user
            throw $e;
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
     * Verify if a message has an issuer that is known as an SP to us. If not, it
     * throws a Corto_Module_Bindings_VerificationException.
     * @param string $messageIssuer
     * @param string $destination
     * @return AbstractRole Remote Entity that issued the message
     * @throws EngineBlock_Corto_Exception_UnknownIssuer
     */
    protected function _verifyKnownSP($messageIssuer, $destination = '')
    {
        $remoteEntity = $this->_server->getRepository()->findServiceProviderByEntityId($messageIssuer);

        if ($remoteEntity) {
            return $remoteEntity;
        }

        $this->_logger->notice(
            sprintf(
                'Tried to verify a message from issuer "%s", but there is no known SP with that ID.',
                $messageIssuer
            )
        );

        throw new EngineBlock_Corto_Exception_UnknownIssuer(
            "Issuer '{$messageIssuer}' is not a known remote entity? (please add SP to Remote Entities)",
            $messageIssuer,
            $destination
        );
    }

    /**
     * Verify if a message has an issuer that is known to us. If not, it
     * throws a Corto_Module_Bindings_VerificationException.
     * @param string $messageIssuer
     * @param string $destination
     * @return AbstractRole Remote Entity that issued the message
     * @throws EngineBlock_Corto_Exception_UnknownIssuer
     */
    protected function _verifyKnownIdP($messageIssuer, $destination = '')
    {
        $remoteEntity = $this->_server->getRepository()->findIdentityProviderByEntityId($messageIssuer);

        if ($remoteEntity) {
            return $remoteEntity;
        }

        $this->_logger->notice(
            sprintf(
                'Tried to verify a message from issuer "%s", but there is no known IdP entity with that ID.',
                $messageIssuer
            )
        );

        throw new EngineBlock_Corto_Exception_UnknownIssuer(
            "Issuer '{$messageIssuer}' is not a known remote entity? (please add IdP to Remote Entities)",
            $messageIssuer,
            $destination
        );
    }

    /**
     * Check if the IssueInstant of the message is too far out of sync
     * @param integer $issueInstant
     * @param string $type
     * @param string $entityid
     */
    protected function _checkIssueInstant($issueInstant, $type, $entityid)
    {
        // check the IssueInstant against our own time to see if the SP's clock is getting out of sync
        // Ssp has a hard-coded limit of 60 seconds; use 30 here to catch an IdP's drifting clock early
        $time = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getTimeProvider()->time();
        $timeDelta = $time - $issueInstant;
        if (abs($timeDelta) > 30) {
            $this->_logger->notice(
                sprintf(
                    'IssueInstant of SAML message from %s "%s" is %d seconds in the %s; might indicate (local or remote) clock synchronization issues',
                    $type,
                    $entityid,
                    abs($timeDelta),
                    $timeDelta>0 ? "past" : "future"
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
            $sspMessage->setSignatureKey(
                $keyPair->getPrivateKey()->toXmlSecurityKey(
                    $remoteEntity->signatureMethod
                )
            );
        }

        $sspBinding = Binding::getBinding($bindingUrn);
        if ($sspBinding instanceof HTTPPost) {

            // SAML2int dictates that we MUST sign assertions.
            // The SAML2 library will do that for us, if we just set the key to sign with.
            if ($sspMessage instanceof Response) {
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

            // If the processed assertion consumer service is set on the response, it is posted back to the SP using the
            // the 'return' hidden form field.
            if (method_exists($message, 'getReturn') && !empty(trim($message->getReturn()))) {
                $extra .= '<input type="hidden" name="return" value="' . htmlspecialchars($message->getReturn()) . '">';
            }

            $extra .= $sspMessage->getRelayState()           ? '<input type="hidden" name="RelayState" value="' . htmlspecialchars($sspMessage->getRelayState()) . '">' : '';

            $encodedMessage = htmlspecialchars(base64_encode($xml));

            $action = $sspMessage->getDestination();

            $log = $this->_server->getLogger();
            $log->info('HTTP-Post: Sending Message', array('saml_message' => $xml));

            $type = $message->getMessageType();

            if (!isset($action)) {
                throw new EngineBlock_View_Exception('No action given to HTTP Post screen');
            }
            if (!isset($type)) {
                throw new EngineBlock_View_Exception('No message type (SAMLRequest or SAMLResponse) given to HTTP Post screen');
            }
            if (!isset($encodedMessage)) {
                throw new EngineBlock_View_Exception('No message given to HTTP Post screen');
            }

            $output = $this->twig->render(
                '@theme/Authentication/View/Proxy/form.html.twig',
                [
                    'action' => $action,
                    'message' => $encodedMessage,
                    'xtra' => $extra,
                    'name' => $type,
                    'trace' => $this->getTraceHtml($xml),
                ]
            );
            $this->_server->sendOutput($output);

        } else if ($sspBinding instanceof HTTPRedirect) {
            if ($sspMessage instanceof Response) {
                throw new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException(
                    'May not send a response via HTTP redirect'
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
            $dom = DOMDocumentFactory::fromString($xml);
            if (!$dom->schemaValidate($schemaUrl)) {
                throw new Exception('Message XML doesnt validate against XSD at Oasis-open.org?!');
            }
        }
    }

    protected function shouldMessageBeSigned(Message $sspMessage, AbstractRole $remoteEntity)
    {
        if ($sspMessage instanceof Response) {
            return true;
        }

        if (!$sspMessage instanceof AuthnRequest) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsupported Message type: ' . get_class($sspMessage)
            );
        }

        // Determine if we should sign the message
        return $remoteEntity->requestsMustBeSigned || $this->_server->getConfig('WantsAuthnRequestsSigned');
    }

    private function assertValidAcsLocationScheme($acsLocation)
    {
        if ($acsLocation && !$this->acsLocationSchemeValidator->validate($acsLocation)) {
            $this->_logger->warning(sprintf('The received ACS location "%s" does not have a valid scheme', $acsLocation));
        }
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

        /** @var Message $message */
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
     * @param IdentityProvider $idp
     * @param bool $requireEncryption
     * @return \SAML2\Configuration\IdentityProvider
     */
    protected function mapEngineBlockIdpToSaml2Idp(IdentityProvider $idp, $requireEncryption = true)
    {
        /** @var EngineBlock_X509_Certificate[] $certificates */
        $certificates = $idp->certificates;

        $config = array(
            'entityId'                   => $idp->entityId,
            'keys'                       => array(),
            'assertionEncryptionEnabled' => $requireEncryption,
        );
        foreach ($certificates as $certificate) {
            $config['keys'][] = array(
                'signing'                    => true,
                'type'                       => 'X509Certificate',
                'X509Certificate'            => $certificate->toCertData(),
            );
        }

        return new Saml2IdentityProvider($config);
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @param bool $requireEncryption
     * @return Saml2ServiceProvider
     */
    protected function mapEngineBlockSpToSaml2Sp(ServiceProvider $serviceProvider, $requireEncryption = true)
    {
        /** @var EngineBlock_X509_Certificate[] $certificates */
        $certificates = $serviceProvider->certificates;

        $config = array(
            'entityId'                   => $serviceProvider->entityId,
            'keys'                       => array(),
            'assertionEncryptionEnabled' => $requireEncryption,
        );

        foreach ($certificates as $certificate) {
            $config['keys'][] = array(
                'signing'         => true,
                'type'            => 'X509Certificate',
                'X509Certificate' => $certificate->toCertData(),
            );
        }

        return new Saml2ServiceProvider($config);
    }

    /**
     * @param bool $requireEncryption
     * @return Saml2ServiceProvider
     */
    protected function getSaml2OwnMetadata($requireEncryption = true)
    {
        $keyPair = $this->_server->getSigningCertificates();
        $config = array(
            'entityId'                   => $this->_server->getUrl('spMetadataService'),
            'assertionEncryptionEnabled' => $requireEncryption,
            'keys'                       => array(
                array(
                    'signing'         => true,
                    'type'            => 'X509Certificate',
                    'X509Certificate' => $keyPair->getCertificate()->toCertData(),
                ),
            ),
        );

        $privateKey = $keyPair->getPrivateKey();

        if ($privateKey) {
            $config['privateKeys'][] = new PrivateKey(
                $privateKey->filePath(),
                PrivateKey::NAME_DEFAULT
            );
        }

        return new Saml2ServiceProvider($config);
    }

    /**
     * Determines if a Response carries an encrypted assertion.
     *
     * @param Response $sspResponse
     * @return bool
     */
    private function hasEncryptedAssertion(Response $sspResponse)
    {
        $hasEncryptedAssertion = false;
        foreach ($sspResponse->getAssertions() as $assertion) {
            if ($assertion instanceof EncryptedAssertion) {
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
