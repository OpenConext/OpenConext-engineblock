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
            throw new EngineBlock_Corto_Module_Bindings_Exception(
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
            throw new EngineBlock_Corto_Module_Bindings_Exception(
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

    /**
     * Encrypt an element using a particular public key.
     * @param String $publicKey The public key used for encryption.
     * @param array  $element An array representation of an xml fragment
     * @param string $tag Element name for the root element
     * @return array The encrypted version of the element.
     */
    protected function _encryptElement($publicKey, $element, $tag = null)
    {
        if ($tag) {
            $element['__t'] = $tag;
        }
        $data = EngineBlock_Corto_XmlToArray::array2xml($element);
        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($cipher), MCRYPT_DEV_URANDOM);
        $sessionkey = mcrypt_create_iv(mcrypt_enc_get_key_size($cipher), MCRYPT_DEV_URANDOM);
        mcrypt_generic_init($cipher, $sessionkey, $iv);
        $encryptedData = $iv . mcrypt_generic($cipher, $data);
        mcrypt_generic_deinit($cipher);
        mcrypt_module_close($cipher);

        $publicKey = openssl_pkey_get_public($publicKey);
        openssl_public_encrypt($sessionkey, $encryptedKey, $publicKey, OPENSSL_PKCS1_PADDING);
        openssl_free_key($publicKey);

        $encryptedElement = array(
            'xenc:EncryptedData' => array(
                '_xmlns:xenc' => 'http://www.w3.org/2001/04/xmlenc#',
                '_Type' => 'http://www.w3.org/2001/04/xmlenc#Element',
                'ds:KeyInfo' => array(
                    '_xmlns:ds' => "http://www.w3.org/2000/09/xmldsig#",
                    'xenc:EncryptedKey' => array(
                        '_Id' => $this->_server->getNewId(),
                        'xenc:EncryptionMethod' => array(
                            '_Algorithm' => "http://www.w3.org/2001/04/xmlenc#rsa-1_5"
                        ),
                        'xenc:CipherData' => array(
                            'xenc:CipherValue' => array(
                                '__v' => base64_encode($encryptedKey),
                            ),
                        ),
                    ),
                ),
                'xenc:EncryptionMethod' => array(
                    '_Algorithm' => 'http://www.w3.org/2001/04/xmlenc#aes128-cbc',
                ),
                'xenc:CipherData' => array(
                    'xenc:CipherValue' => array(
                        '__v' => base64_encode($encryptedData),
                    ),
                ),
            ),
        );
        return $encryptedElement;
    }

    public function send(array $message, array $remoteEntity)
    {
        $bindingUrn = $message['__']['ProtocolBinding'];

        if (!$this->isSupportedBinding($bindingUrn)) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unknown binding: '. $bindingUrn,
                EngineBlock_Exception::CODE_ERROR
            );
        }
        $function = $this->_bindings[$bindingUrn];

        $this->$function($message, $remoteEntity);
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

    /**
     * Redirects a message
     *
     * Note response redirecting is currently broken for response redirection to fix this do the following:
     * - Refactor the signing related parts so that $mustSign is also true for redirect responses
     * - Configure correct signing algorithm: 'http://www.w3.org/2000/09/xmldsig#rsa-sha1'
     * - Change signature removal so that the signature is removed from the assertion instead of from the message
     *
     * @param array $message
     * @param array $remoteEntity
     */
    protected function _sendHTTPRedirect(array $message, $remoteEntity)
    {
        $messageType = $message['__']['paramname'];

        if ($messageType === self::KEY_RESPONSE) {
            throw new EngineBlock_Corto_Module_Bindings_UnsupportedBindingException('Redirecting response is not supported');
        }

        // Determine if we should sign the message
        $wantRequestsSigned = (
            // If the destination wants the AuthnRequests signed
            (isset($remoteEntity['AuthnRequestsSigned']) && $remoteEntity['AuthnRequestsSigned'])
                ||
                // Or we currently demand that all AuthnRequests are sent signed
                $this->_server->getConfig('WantsAuthnRequestsSigned')
        );
        $mustSign = ($messageType===self::KEY_REQUEST && $wantRequestsSigned);
        if ($mustSign) {
            $this->_server->getSessionLog()->info("HTTP-Redirect: Removing signature");
            unset($message['ds:Signature']);
        }

        // Encode the message in destination format
        if ($message['__']['ProtocolBinding'] == 'JSON-Redirect') {
            $encodedMessage = json_encode($message);
        }
        else {
            $encodedMessage = EngineBlock_Corto_XmlToArray::array2xml($message);
        }

        // Encode the message for transfer
        $encodedMessage = urlencode(base64_encode(gzdeflate($encodedMessage)));

        // Build the query string
        if ($message['__']['ProtocolBinding'] == 'JSON-Redirect') {
            $queryString = "$messageType=$encodedMessage";
        }
        else {
            $queryString = "$messageType=" . $encodedMessage;
        }
        $queryString .= isset($message['__']['RelayState']) ? '&RelayState=' . urlencode($message['__']['RelayState']) : "";

        // Sign the message
        if (isset($remoteEntity['SharedKey'])) {
            $queryString .= "&Signature=" . urlencode(base64_encode(sha1($remoteEntity['SharedKey'] . sha1($queryString))));
        } elseif ($mustSign) {
            $this->_server->getSessionLog()->info("HTTP-Redirect: (Re-)Signing");
            $queryString .= '&SigAlg=' . urlencode($this->_server->getConfig('SigningAlgorithm'));

            $key = $this->_getCurrentEntityPrivateKey();

            $signature = "";
            openssl_sign($queryString, $signature, $key);
            openssl_free_key($key);

            $queryString .= '&Signature=' . urlencode(base64_encode($signature));
        }

        // Build the full URL
        $location = $message['_Destination'] . (isset($message['_Recipient'])?$message['_Recipient']:''); # shib remember ...
        $location .= "?" . $queryString;

        // Redirect
        $this->_server->redirect($location, $message);
    }

    protected function _getCurrentEntityPrivateKey()
    {
        $certificates = $this->_server->getConfig('certificates', array());
        if (!isset($certificates['private'])) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Current entity has no private key, unable to sign message! Please set ["certificates"]["private"]!',
                EngineBlock_Exception::CODE_WARNING
            );
        }
        $key = openssl_pkey_get_private($certificates['private']);
        if ($key === false) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Current entity ['certificates']['private'] value is NOT a valid PEM formatted SSL private key?!? Value: " . $certificates['private'],
                EngineBlock_Exception::CODE_WARNING
            );
        }
        return $key;
    }

    protected function _sendHTTPPost($message, $remoteEntity)
    {
        $name = $message['__']['paramname'];
        $extra = "";
        if ($message['__']['ProtocolBinding'] == 'JSON-POST') {
            if ($relayState = $message['__']['RelayState']) {
                $relayState = "&RelayState=$relayState";
            }
            $name = 'j' . $name;
            $encodedMessage = json_encode($message);
            $signatureHTMLValue = htmlspecialchars(base64_encode(sha1($remoteEntity['sharedkey'] . sha1("$name=$message$relayState"))));
            $extra .= '<input type="hidden" name="Signature" value="' . $signatureHTMLValue . '">';

        } else {
            // Determine if we should sign the message
            $wantRequestsSigned = (
                // If the destination wants the AuthnRequests signed
                (isset($remoteEntity['AuthnRequestsSigned']) && $remoteEntity['AuthnRequestsSigned'])
                    ||
                    // Or we currently demand that all AuthnRequests are sent signed
                    $this->_server->getConfig('WantsAuthnRequestsSigned')
            );
            if ($name == 'SAMLRequest' && $wantRequestsSigned) {
                $this->_server->getSessionLog()->info("HTTP-Redirect: (Re-)Signing");
                $message = $this->_server->sign($message);
            }
            else if ($name == 'SAMLResponse') {
                $this->_server->getSessionLog()->info("HTTP-Redirect: (Re-)Signing Assertion");

                $message['saml:Assertion']['__t'] = 'saml:Assertion';
                $message['saml:Assertion']['_xmlns:saml'] = "urn:oasis:names:tc:SAML:2.0:assertion";
                $message['saml:Assertion']['ds:Signature'] = '__placeholder__';

                uksort($message['saml:Assertion'], array(__CLASS__, '_usortAssertion'));

                $message['saml:Assertion'] = $this->_server->sign($message['saml:Assertion']);
                #$enc = docrypt(certs::$server_crt, $message['saml:Assertion'], 'saml:EncryptedAssertion');

            }
            else if ($name == 'SAMLResponse' && isset($remoteEntity['WantsResponsesSigned']) && $remoteEntity['WantsResponsesSigned']) {
                $this->_server->getSessionLog()->info("HTTP-Redirect: (Re-)Signing");

                uksort($message['saml:Assertion'], array(__CLASS__, '_usortAssertion'));

                $message = $this->_server->sign($message);
            }

            $encodedMessage = EngineBlock_Corto_XmlToArray::array2xml($message);

            $schemaUrl = 'http://docs.oasis-open.org/security/saml/v2.0/saml-schema-protocol-2.0.xsd';
            if ($this->_server->getConfig('debug') && ini_get('allow_url_fopen') && file_exists($schemaUrl)) {
                $dom = new DOMDocument();
                $dom->loadXML($encodedMessage);
                if (!$dom->schemaValidate($schemaUrl)) {
                    //echo '<pre>'.htmlentities(Corto_XmlToArray::formatXml($encodedMessage)).'</pre>';
                    //throw new Exception('Message XML doesnt validate against XSD at Oasis-open.org?!');
                }
            }
        }

        $extra .= isset($message['__']['RelayState']) ? '<input type="hidden" name="RelayState" value="' . htmlspecialchars($message['__']['RelayState']) . '">' : '';
        $extra .= isset($message['__']['return'])     ? '<input type="hidden" name="return" value="'     . htmlspecialchars($message['__']['return']) . '">' : '';
        $encodedMessage = htmlspecialchars(base64_encode($encodedMessage));

        $action = $message['_Destination'] . (isset($message['_Recipient'])?$message['_Recipient']:'');

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

    protected function _sign($key, $element)
    {
        $signature = array(
            '__t' => 'ds:Signature',
            '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
            'ds:SignedInfo' => array(
                '__t' => 'ds:SignedInfo',
                '_xmlns:ds' => 'http://www.w3.org/2000/09/xmldsig#',
                'ds:CanonicalizationMethod' => array(
                    '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                ),
                'ds:SignatureMethod' => array(
                    '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
                ),
                'ds:Reference' => array(
                    0 => array(
                        '_URI' => '__placeholder__',
                        'ds:Transforms' => array(
                            'ds:Transform' => array(
                                array(
                                    '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                                ),
                                array(
                                    '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                                ),
                            ),
                        ),
                    ),
                    'ds:DigestMethod' => array(
                        '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1',
                    ),
                    'ds:DigestValue' => array(
                        '__v' => '__placeholder__',
                    ),
                ),
            ),
            'ds:SignatureValue' => array(
                '__v' => '__placeholder__',
            )
        );

        $canonicalXml = DOMDocument::loadXML(EngineBlock_Corto_XmlToArray::array2xml($element))->firstChild->C14N(true, false);

        $signature['ds:SignedInfo']['ds:Reference'][0]['ds:DigestValue']['__v'] = base64_encode(sha1($canonicalXml, TRUE));
        $signature['ds:SignedInfo']['ds:Reference'][0]['_URI'] = "#" . $element['_ID'];

        $canonicalXml2 = DOMDocument::loadXML(EngineBlock_Corto_XmlToArray::array2xml($signature['ds:SignedInfo']))->firstChild->C14N(true, false);

        openssl_sign($canonicalXml2, $signatureValue, $key);

        openssl_free_key($key);

        $signature['ds:SignatureValue']['__v'] = base64_encode($signatureValue);

        $newElement = $element;
        foreach ($element as $tag => $item) {
            if ($tag == 'ds:Signature') {
                continue;
            }

            $newElement[$tag] = $item;

            if ($tag == 'saml:Issuer') {
                $newElement['ds:Signature'] = $signature;
            }
        }

        return $newElement;
    }

    protected static function _usortAssertion($a, $b)
    {
        $result = self::_usortByPrefix(EngineBlock_Corto_XmlToArray::PRIVATE_PFX, $a, $b);
        if ($result !== false) {
            return $result;
        }

        $result = self::_usortByPrefix(EngineBlock_Corto_XmlToArray::TAG_NAME_PFX, $a, $b);
        if ($result !== false) {
            return $result;
        }

        $result = self::_usortByPrefix('_xmlns', $a, $b);
        if ($result !== false) {
            return $result;
        }

        $result = self::_usortByPrefix(EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX, $a, $b);
        if ($result !== false) {
            return $result;
        }

        $result = self::_usortBySequence(self::$ASSERTION_SEQUENCE, $a, $b);
        if ($result !== false) {
            return $result;
        }

        // Finally, something else...? Should never get here
        return strcmp($a, $b);
    }

    protected static function _usortByPrefix($prefix, $a, $b)
    {
        // private key first
        $aHasPrefix = (strpos($a, $prefix) === 0);
        $bHasPrefix = (strpos($b, $prefix) === 0);
        if ($aHasPrefix && !$bHasPrefix) {
            return -1;
        }
        else if ($bHasPrefix && !$aHasPrefix) {
            return 1;
        }
        else if ($aHasPrefix && $bHasPrefix) {
            return strcmp($a, $b);
        }
        return false;
    }

    protected static function _usortBySequence($sequence, $a, $b)
    {
        // regular tags fifth
        $aInSequence = in_array($a, $sequence, false);
        $bInSequence = in_array($b, $sequence, false);
        if ($aInSequence && !$bInSequence) {
            return -1;
        }
        else if ($bInSequence && !$aInSequence) {
            return 1;
        }
        else if ($aInSequence && $bInSequence) {
            return array_search($a, $sequence) > array_search($b, $sequence) ? 1 : -1;
        }
        return false;
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
