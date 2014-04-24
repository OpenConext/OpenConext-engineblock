<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * The bindings module for Corto, which implements support for various data
 * bindings.
 * @author Boy
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

    public function __construct(EngineBlock_Corto_ProxyServer $server)
    {
        parent::__construct($server);

        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $this->_sspmodSamlMessageClassName = $diContainer->getMessageUtilClassName();
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
        $requestXml = $sspRequest->toUnsignedXML()->ownerDocument->saveXML();

        // Log the request we received for troubleshooting
        $log = $this->_server->getSessionLog();
        $log->attach($requestXml, 'Request')
            ->info('Received request');

        if (!($sspRequest instanceof SAML2_AuthnRequest)) {
            throw new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException(
                'Unsupported Binding used',
                EngineBlock_Exception::CODE_NOTICE
            );
        }

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
        $cortoSpMetadata = $this->_verifyKnownMessageIssuer(
            $spEntityId,
            $ebRequest->getDestination()
        );

        // Load the metadata for this IdP in SimpleSAMLphp style
        $sspSpMetadata = SimpleSAML_Configuration::loadFromArray(
            $this->mapCortoEntityMetadataToSspEntityMetadata($cortoSpMetadata)
        );

        // Determine if we should check the signature of the message
        $wantRequestsSigned = (
            // If the destination wants the AuthnRequests signed
            (isset($cortoSpMetadata['AuthnRequestsSigned']) && $cortoSpMetadata['AuthnRequestsSigned'])
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

        $this->_annotateRequestWithVoContext($ebRequest, $cortoSpMetadata);

        $this->_annotateRequestWithKeyId($ebRequest);

        return $ebRequest;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $ebRequest
     * @param array $cortoSpMetadata
     * @return void
     * @throws EngineBlock_Corto_Exception_VoMismatch
     */
    protected function _annotateRequestWithVoContext(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $ebRequest,
        array $cortoSpMetadata
    ) {
        // Check if the request was received on a VO endpoint.
        $explicitVo = $this->_server->getVirtualOrganisationContext();

        // Check if the SP should always use a VO (implicit VO).
        $implicitVo = NULL;
        if (isset($cortoSpMetadata['VoContext']) && $cortoSpMetadata['VoContext']) {
            $implicitVo = $cortoSpMetadata['VoContext'];
        }

        // If we have neither, then we're done here
        if (!$explicitVo && !$implicitVo) {
            return;
        }

        // If we have both then they'd better match!
        if ($explicitVo && $implicitVo && $explicitVo !== $implicitVo) {
            throw new EngineBlock_Corto_Exception_VoMismatch(
                "Explicit VO '$explicitVo' does not match implicit VO '$implicitVo'!"
            );
        }

        // If we received the request on a vo endpoint, then we should register it in the metadata,
        // so we know to use that as Issuer of the resulting Response.
        // And the implicit VO no longer matters.
        if ($explicitVo) {
            $ebRequest->setExplicitVoContext($explicitVo);
            return;
        }

        // If we received the request from an SP with an implicit VO, then register it in the metadata,
        // so it can be verified.
        if ($implicitVo) {
            $ebRequest->setImplicitVoContext($implicitVo);
            return;
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

        // We only support HTTP-Post and HTTP-Redirect bindings
        if (!($sspBinding instanceof SAML2_HTTPPost || $sspBinding instanceof SAML2_HTTPRedirect)) {
            throw new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException(
                'Unsupported Binding used',
                EngineBlock_Exception::CODE_NOTICE
            );
        }
        /** @var SAML2_HTTPPost|SAML2_HTTPRedirect $sspBinding */

        // Receive a message from the binding
        $sspResponse = $sspBinding->receive();
        if (!($sspResponse instanceof SAML2_Response)) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsupported Message received',
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        // Log the response we received for troubleshooting
        $log = $this->_server->getSessionLog();
        $log->attach($sspResponse->toUnsignedXML()->ownerDocument->saveXML(), 'Response')
            ->info('Received response');

        // This message MUST be a SAML2 response, we don't want a AuthnRequest, LogoutResponse, etc.
        if (!($sspResponse instanceof SAML2_Response)) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Invalid message received to AssertionConsumerService endpoint.'
            );
        }

        // Make sure the response from the idp has an Issuer
        $idpEntityId = $sspResponse->getIssuer();
        if ($idpEntityId === NULL) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Missing <saml:Issuer> in message delivered to AssertionConsumerService.'
            );
        }

        // Remember idp for debugging
        $_SESSION['currentIdentityProvider'] = $idpEntityId;

        // Verify that we know this IdP and have metadata for it.
        $cortoIdpMetadata = $this->_verifyKnownMessageIssuer(
            $idpEntityId,
            isset($message['_Destination']) ? $message['_Destination'] : ''
        );

        // Load the metadata for this IdP in SimpleSAMLphp style
        $sspIdpMetadata = SimpleSAML_Configuration::loadFromArray(
            $this->mapCortoEntityMetadataToSspEntityMetadata($cortoIdpMetadata)
        );

        // Make sure it has a InResponseTo (Unsollicited is not supported) but don't actually check that what it's
        // in response to is actually a message we sent quite yet.
        if (!$sspResponse->getInResponseTo()) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsollicited assertion (no InResponseTo in message) not supported!'
            );
        }

        try {
            // 'Process' the response, verify the signature, verify the timings.
            $className = $this->_sspmodSamlMessageClassName;
            $assertions = $className::processResponse($sspSpMetadata, $sspIdpMetadata, $sspResponse);

            // We only support 1 assertion
            if (count($assertions) > 1) {
                throw new EngineBlock_Corto_Module_Bindings_Exception(
                    'More than one assertion in received response.',
                    EngineBlock_Exception::CODE_NOTICE
                );
            }

            $sspResponse->setAssertions($assertions);
        }
        // This misnamed exception is only thrown when the Response status code is not Success
        // If so, let the Corto Output Filters handle it.
        catch (sspmod_saml_Error $e) {
            $log->attach($e->getMessage(), 'exception message')
                ->attach($e->getStatus(), 'status')
                ->attach($e->getSubStatus(), 'substatus')
                ->attach($e->getStatusMessage(), 'status message')
                ->notice('Received an Error Response');
        }
        // Thrown when timings are out of whack or other some such verification exceptions.
        catch (SimpleSAML_Error_Exception $e) {
            throw new EngineBlock_Corto_Module_Bindings_VerificationException(
                $e->getMessage(),
                EngineBlock_Exception::CODE_NOTICE,
                $e
            );
        }
        // General Response whackiness (like Destinations not matching)
        catch (Exception $e) {
            throw new EngineBlock_Corto_Module_Bindings_VerificationException(
                $e->getMessage(),
                EngineBlock_Exception::CODE_NOTICE,
                $e
            );
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
     * @param array $messageIssuer
     * @param string $destination
     * @return array Remote Entity that issued the message
     * @throws EngineBlock_Corto_Exception_UnknownIssuer
     */
    protected function _verifyKnownMessageIssuer($messageIssuer, $destination = '')
    {
        try {
            $remoteEntity = $this->_server->getRemoteEntity($messageIssuer);
        } catch (EngineBlock_Corto_ProxyServer_Exception $e) {
            throw new EngineBlock_Corto_Exception_UnknownIssuer(
                "Issuer '{$messageIssuer}' is not a known remote entity? (please add SP/IdP to Remote Entities)",
                $messageIssuer,
                $destination
            );
        }
        return $remoteEntity;
    }

    public function send(
        EngineBlock_Saml2_MessageAnnotationDecorator $message,
        array $remoteEntity
    ) {
        $bindingUrn = $message->getDeliverByBinding();
        $sspMessage = $message->getSspMessage();

        if ($bindingUrn === 'INTERNAL') {
            $this->sendInternal($message);
            return;
        }

        if ($this->shouldMessageBeSigned($sspMessage, $remoteEntity)) {
            $certificates = $this->_server->getSigningCertificates();

            if (isset($certificates['privateFile'])) {
                $privateKeyIsFile = true;
                $privateKey = $certificates['privateFile'];
            }
            else {
                $privateKeyIsFile = false;
                $privateKey = $certificates['private'];
            }

            $privateKeyObj = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
            $privateKeyObj->loadKey($privateKey, $privateKeyIsFile);

            $sspMessage->setCertificates(array($certificates['public']));
            $sspMessage->setSignatureKey($privateKeyObj);
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

            $log = $this->_server->getSessionLog();
            $log->attach($message, 'SAML message')
                ->info('HTTP-Post: Sending Message');

            $output = $this->_server->renderTemplate(
                'form',
                array(
                    'action' => $action,
                    'message' => $encodedMessage,
                    'xtra' => $extra,
                    'name' => $message->getMessageType(),
                    'trace' => $this->_server->getConfig('debug', false) ? htmlentities($xml) : '',
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
            $dom = new DOMDocument();
            $dom->loadXML($xml);
            if (!$dom->schemaValidate($schemaUrl)) {
                throw new Exception('Message XML doesnt validate against XSD at Oasis-open.org?!');
            }
        }
    }

    protected function shouldMessageBeSigned(SAML2_Message $sspMessage, array $remoteEntity)
    {
        if ($sspMessage instanceof SAML2_Response) {
            return true;
        }

        if (!($sspMessage instanceof SAML2_AuthnRequest)) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsupported Message type: ' . get_class($sspMessage)
            );
        }

        // Determine if we should sign the message
        $destinationWantsSignature = (isset($remoteEntity['AuthnRequestsSigned']) && $remoteEntity['AuthnRequestsSigned']);
        $weRequireSignatureOnRequests = $this->_server->getConfig('WantsAuthnRequestsSigned');
        return $destinationWantsSignature || $weRequireSignatureOnRequests;
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

        $log = $this->_server->getSessionLog();
        $log->attach($parameters, 'URL Params')
            ->info("Using internal binding for destination: $destinationLocation, resulting in parameters:");

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
     * @param $cortoEntityMetadata
     * @return array
     */
    protected function mapCortoEntityMetadataToSspEntityMetadata($cortoEntityMetadata)
    {
        $publicPems = array($cortoEntityMetadata['certificates']['public']);
        if (isset($cortoEntityMetadata['certificates']['public-fallback'])) {
            $publicPems[] = $cortoEntityMetadata['certificates']['public-fallback'];
        }
        if (isset($cortoEntityMetadata['certificates']['public-fallback2'])) {
            $publicPems[] = $cortoEntityMetadata['certificates']['public-fallback2'];
        }
        $publicPems = str_replace(
            array('-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\t", " "),
            '',
            $publicPems
        );

        $config = array(
            'entityid'            => $cortoEntityMetadata['EntityID'],
            'keys'                => array(),
        );
        if (isset($cortoEntityMetadata['SingleSignOnService']['Location'])) {
            $config['SingleSignOnService'] = $cortoEntityMetadata['SingleSignOnService']['Location'];
        }
        if (isset($cortoEntityMetadata['AssertionConsumerServices'][0]['Location'])) {
            $config['AssertionConsumerService'] = $cortoEntityMetadata['AssertionConsumerServices'][0]['Location'];
        }
        foreach ($publicPems as $publicPem) {
            $config['keys'][] = array(
                'signing'         => true,
                'type'            => 'X509Certificate',
                'X509Certificate' => $publicPem,
            );
        }
        return $config;
    }

    /**
     * @return SimpleSAML_Configuration
     */
    protected function getSspOwnMetadata()
    {
        $configs   = $this->_server->getConfigs();
        $publicPem = $configs['certificates']['public'];
        $publicPem = str_replace(
            array('-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\t", " "),
            '',
            $publicPem
        );

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
                        'X509Certificate' => $publicPem,
                    ),
                    array(
                        'signing'         => true,
                        'type'            => 'X509Certificate',
                        'X509Certificate' => $publicPem,
                    ),
                ),
            )
        );
        return $spMetadata;
    }
}
