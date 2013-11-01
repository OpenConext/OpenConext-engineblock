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

        // Make sure the request from the sp has an Issuer
        $spEntityId = $sspRequest->getIssuer();
        if (!$spEntityId) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Missing <saml:Issuer> in message delivered to AssertionConsumerService.'
            );
        }
        // Remember sp for debugging
        $_SESSION['currentServiceProvider'] = $sspRequest->getIssuer();

        // Verify that we know this SP and have metadata for it.
        $cortoSpMetadata = $this->_verifyKnownMessageIssuer(
            $spEntityId,
            $sspRequest->getDestination()
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
        $wasSigned = false;
        if ($wantRequestsSigned) {
            // Check the Signature on the Request, if there is no signature, or verification fails
            // throw an exception.
            if (!sspmod_saml_Message::checkSign($sspSpMetadata, $sspRequest)) {
                throw new EngineBlock_Corto_Module_Bindings_VerificationException(
                    'Validation of received messages enabled, but no signature found on message.'
                );
            }
            // Otherwise validation succeeded.
            $wasSigned = true;
        }

        // Convert the SSP Request to a Corto Request
        $cortoRequest = EngineBlock_Corto_XmlToArray::xml2array($requestXml);
        $cortoRequest['__'] = array(
            'ProtocolBinding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
            'RelayState'      => $sspRequest->getRelayState(),
            'paramname'       => 'SAMLResponse',
            'WasSigned'       => $wasSigned,
        );

        $cortoRequest = $this->_annotateRequestWithVoContext($cortoRequest, $cortoSpMetadata);

        return $cortoRequest;
    }

    /**
     * @param array $cortoRequest
     * @param array $cortoSpMetadata
     * @return array
     * @throws EngineBlock_Corto_Exception_VoMismatch
     */
    protected function _annotateRequestWithVoContext(array $cortoRequest, array $cortoSpMetadata)
    {
        // Check if the request was received on a VO endpoint.
        $explicitVo = $this->_server->getVirtualOrganisationContext();

        // Check if the SP should always use a VO (implicit VO).
        $implicitVo = NULL;
        if (isset($cortoSpMetadata['VoContext']) && $cortoSpMetadata['VoContext']) {
            $implicitVo = $cortoSpMetadata['VoContext'];
        }

        // If we have neither, then we're done here
        if (!$explicitVo && !$implicitVo) {
            return $cortoRequest;
        }

        // If we have both then they'd better match!
        if ($explicitVo && $implicitVo && $explicitVo !== $implicitVo) {
            throw new EngineBlock_Corto_Exception_VoMismatch(
                "Explicit VO '$explicitVo' does not match implicit VO '$implicitVo'!"
            );
        }

        $requestMetadata = &$cortoRequest[EngineBlock_Corto_XmlToArray::PRIVATE_PFX];

        // If we received the request on a vo endpoint, then we should register it in the metadata,
        // so we know to use that as Issuer of the resulting Response.
        // And the implicit VO no longer matters.
        if ($explicitVo) {
            $requestMetadata[EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX] = $explicitVo;
            return $cortoRequest;
        }

        // If we received the request from an SP with an implicit VO, then register it in the metadata,
        // so it can be verified.
        if ($implicitVo) {
            $requestMetadata[EngineBlock_Corto_ProxyServer::VO_CONTEXT_IMPLICIT] = $implicitVo;
        }
        return $cortoRequest;
    }

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
        $responseXml = $sspResponse->toUnsignedXML()->ownerDocument->saveXML();

        // Log the response we received for troubleshooting
        $log = $this->_server->getSessionLog();
        $log->attach($responseXml, 'Response')
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
            $assertions = sspmod_saml_Message::processResponse($sspSpMetadata, $sspIdpMetadata, $sspResponse);

            // We only support 1 assertion
            if (count($assertions) > 1) {
                throw new EngineBlock_Corto_Module_Bindings_Exception(
                    'More than one assertion in received response.',
                    EngineBlock_Exception::CODE_NOTICE
                );
            }
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

        $cortoResponse = EngineBlock_Corto_XmlToArray::xml2array($responseXml);
        $cortoResponse['__'] = array(
            'ProtocolBinding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'RelayState'      => $sspResponse->getRelayState(),
            'paramname'       => 'SAMLResponse',
        );
        return $cortoResponse;
    }

    protected function _receiveMessageFromInternalBinding($key)
    {
        if (!isset($this->_internalBindingMessages[$key]) || !is_array($this->_internalBindingMessages[$key])) {
            return false;
        }

        $message = $this->_internalBindingMessages[$key];
        $message[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Binding'] = "INTERNAL";
        return $message;
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

    public function send(array $message, array $remoteEntity)
    {
        $bindingUrn = $message['__']['ProtocolBinding'];

        if ($bindingUrn === 'INTERNAL') {
            $this->sendInternal($message, $remoteEntity);
            return;
        }

        // Convert Corto Message to SSP message
        $xml = EngineBlock_Corto_XmlToArray::array2xml($message);
        $document = new DOMDocument();
        $document->loadXML($xml);
        $sspMessage = SAML2_Message::fromXML($document->firstChild);

        if ($this->shouldMessageBeSigned($sspMessage, $remoteEntity)) {
            $certificates = $this->_server->getConfig('certificates');

            $privateKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
            $privateKey->loadKey($certificates['private']);

            $sspMessage->setSignatureKey($privateKey);
        }

        $sspBinding = SAML2_Binding::getBinding($bindingUrn);
        if ($sspBinding instanceof SAML2_HTTPPost) {

            $messageElement = $sspMessage->toSignedXML();
            $xml = $messageElement->ownerDocument->saveXML($messageElement);

            $this->validateXml($xml);

            $name = $message['__']['paramname'];

            $extra = '';
            $extra .= isset($message['__']['RelayState']) ? '<input type="hidden" name="RelayState" value="' . htmlspecialchars($message['__']['RelayState']) . '">' : '';
            $extra .= isset($message['__']['return'])     ? '<input type="hidden" name="return" value="'     . htmlspecialchars($message['__']['return']) . '">' : '';
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
                    'name' => $name,
                    'trace' => $this->_server->getConfig('debug', false) ? htmlentities(EngineBlock_Corto_XmlToArray::formatXml(EngineBlock_Corto_XmlToArray::array2xml($message))) : '',
                ));
            $this->_server->sendOutput($output);

        } else if ($sspBinding instanceof SAML2_HTTPRedirect) {
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
                throw new Exception('Messagge = Ee XML doesnt validate against XSD at Oasis-open.org?!');
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

    public function sendInternal($message, $remoteEntity)
    {
        // Store the message
        $name            = $message['__']['paramname'];
        $this->_internalBindingMessages[$name] = $message;

        $destinationLocation = $message['_Destination'];
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
