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

require ENGINEBLOCK_FOLDER_VENDOR . 'simplesamlphp/simplesamlphp/lib/_autoload.php';

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

    protected $_internalBindingMessages = array();

    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    protected $_server;

    /**
     * Process an incoming SAML request message. The data is retrieved automatically
     * depending on the binding used.
     */
    public function receiveRequest()
    {
        //return $this->receive();
        $request = $this->_receiveMessage(self::KEY_REQUEST);

        // Remember idp for debugging
        $_SESSION['currentServiceProvider'] = $request['saml:Issuer']['__v'];

        $this->_server->getSessionLog()
            ->attach($request, 'Request')
            ->info('Received request');

        $this->_verifyRequest($request);
        $this->_c14nRequest($request);

        return $request;
    }

    public function receiveResponse()
    {
        // First check if we parked a Response somewhere in memory and are just faking a SSO
        if ($sspResponse = $this->_receiveMessageFromInternalBinding(self::KEY_RESPONSE)) {
            // If so, no need to do any further verification, we trust our own responses.
            return $sspResponse;
        }

        // Compose the metadata for the 'SP' (which is us in this case) for use by SSP.
        $sspSpMetadata = $this->getSspSpMetadata();

        // Detect the binding being used from the global variables (GET, POST, SERVER)
        $sspBinding = SAML2_Binding::getCurrentBinding();

        // We only support HTTP-Post and HTTP-Redirect bindings
        if (!($sspBinding instanceof SAML2_HTTPPost || $sspBinding instanceof SAML2_HTTPRedirect)) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Unsupported Binding used'
            );
        }
        /** @var SAML2_HTTPPost|SAML2_HTTPRedirect $sspBinding */

        // Receive a message from the binding
        $sspResponse = $sspBinding->receive();

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

        // Log the response we received for troubleshooting
        $log = $this->_server->getSessionLog();
        $log->attach($sspResponse->toUnsignedXML(), 'Response')
            ->info('Received response');

        // Verify that we know this IdP and have metadata for it.
        $cortoIdpMetadata = $this->_verifyKnownMessageIssuer(
            $idpEntityId,
            isset($message['_Destination']) ? $message['_Destination'] : ''
        );

        // Load the metadata for this IdP in SimpleSAMLphp style
        $sspIdpMetadata = SimpleSAML_Configuration::loadFromArray(
            $this->mapCortoIdpMetadataToSspIdpMetadata($cortoIdpMetadata)
        );

        // Create a simple Corto response out of this (without assertion)
        $cortoResponse = $this->initCortoResponse($sspResponse);

        // Make sure it has a InResponseTo (Unsollicited is not supported) but don't actually check that what it's
        // in response to is actually a message we sent quite yet.
        $this->_verifyInResponseTo($cortoResponse);

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
            $assertion = $assertions[0];
        }
        // This misnamed exception is only thrown when the Response status code is not Success
        // If so then we don't even need to map the Assertion data, we can let the Corto Output Filters handle it.
        catch (sspmod_saml_Error $e) {
            $log->attach($e->getMessage(), 'exception message')
                ->attach($e->getStatus(), 'status')
                ->attach($e->getSubStatus(), 'substatus')
                ->attach($e->getStatusMessage(), 'status message')
                ->notice('Received an Error Response');
            return $cortoResponse;
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

        $cortoResponse = $this->mapSspResponseToCortoResponse($assertion, $cortoResponse, $sspResponse);
        return $cortoResponse;
    }

    /**
     * Retrieve a message of a certain key, depending on the binding used.
     * A number of bindings is tried in sequence. If the message is available
     * as an artifact, then that is used. Else if the message is available as
     * an http binding, that will be used or finally if the message is
     * available via a http redirect binding than that is used.
     * If none are available, then nothing is returned.
     * @param String $key The key to find
     * @return String The message that was received.
     */
    protected function _receiveMessage($key)
    {
        $message = $this->_receiveMessageFromInternalBinding($key);
        if (!empty($message)) {
            return $this->_addVoContextToRequest($key, $message);
        }

        $message = $this->_receiveMessageFromHttpPost($key);
        if (!empty($message)) {
            return $this->_addVoContextToRequest($key, $message);
        }

        $message = $this->_receiveMessageFromHttpRedirect($key);
        if (!empty($message)) {
            return $this->_addVoContextToRequest($key, $message);
        }

        throw new EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException(
            'Unable to receive message: ' . $key
        );
    }

    protected function _addVoContextToRequest($key, array $message)
    {
        if ($key == self::KEY_REQUEST) {
            // We're dealing with a request, on its way towards the idp. If there's a VO context, we need to store it in the request.

            $voContext = $this->_server->getVirtualOrganisationContext();
            if ($voContext != NULL) {
                $message['__'][EngineBlock_Corto_ProxyServer::VO_CONTEXT_PFX] = $voContext;
            }
        }
        return $message;
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
     * Retrieve a message via http post binding.
     * @param String $key The key to look for.
     * @return mixed False if there was no suitable message in this binding
     *               String the message if it was found
     *               An exception if something went wrong.
     */
    protected function _receiveMessageFromHttpPost($key)
    {
        if (!isset($_POST[$key])) {
            return false;
        }

        $message        = base64_decode($_POST[$key]);
        $messageArray   = $this->_getArrayFromReceivedMessage($message);

        $relayState = "";
        if (isset($_POST['RelayState'])) {
            $relayState     = $_POST['RelayState'];
        }
        $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
        $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['RelayState']   = $relayState;
        $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Raw']          = $message;
        $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['paramname']    = $key;
        if (isset($_POST['return'])) {
            $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Return'] = $_POST['return'];
        }

        return $messageArray;
    }

    /**
     * Retrieve a message via http redirect binding.
     * @param String $key The key to look for.
     * @return mixed False if there was no suitable message in this binding
     *               String the message if it was found
     *               An exception if something went wrong.
     */
    protected function _receiveMessageFromHttpRedirect($key)
    {
        if (!isset($_GET[$key])) {
            return false;
        }

        $message = @base64_decode($_GET[$key], true);
        if (!$message) {
            throw new EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException("Message not base64 encoded!");
        }

        $message = @gzinflate($message);
        if (!$message) {
            throw new EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException("Message not gzipped!");
        }

        $messageArray = $this->_getArrayFromReceivedMessage($message);

        if (isset($_GET['RelayState'])) {
            $relayState         = $_GET['RelayState'];
            $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['RelayState'] = $relayState;
        }

        if (isset($_GET['Signature'])) {
            $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Signature']        = $_GET['Signature'];
            $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['SigningAlgorithm'] = $_GET['SigAlg'];
        }

        $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['ProtocolBinding'] = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
        $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['Raw'] = $message;
        $messageArray[EngineBlock_Corto_XmlToArray::PRIVATE_PFX]['paramname'] = $key;

        return $messageArray;
    }

    /**
     * Unmarshall an SAML2 XML Message to an array representation.
     * @param String $message the XML encoded message
     * @return array An array of data that was contained in the message
     */
    protected function _getArrayFromReceivedMessage($message)
    {
        return EngineBlock_Corto_XmlToArray::xml2array($message);
    }

    /**
     * Verify if a request has a valid signature (if required), whether
     * the issuer is a known entity and whether the message is destined for
     * us. Throws an exception if any of these conditions are not met.
     * @param array $request The array with request data
     * @throws EngineBlock_Corto_Module_Bindings_VerificationException if any of the
     * verifications fail
     */
    protected function _verifyRequest(array &$request)
    {
        $remoteEntity = $this->_verifyKnownMessageIssuer(
            $request['saml:Issuer']['__v'],
            isset($request['_Destination']) ? $request['Destination'] : ''
        );
        // Determine if we should sign the message
        $wantRequestsSigned = (
            // If the destination wants the AuthnRequests signed
            (isset($remoteEntity['AuthnRequestsSigned']) && $remoteEntity['AuthnRequestsSigned'])
                ||
                // Or we currently demand that all AuthnRequests are sent signed
                $this->_server->getConfig('WantsAuthnRequestsSigned')
        );
        if ($wantRequestsSigned) {
            $this->_verifySignature($request, self::KEY_REQUEST, true);
            $request['__']['WasSigned'] = true;
        }
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
     * Transform a request array into a canonical form.
     * @param array $request
     */
    protected function _c14nRequest(array &$request)
    {
        $request['_ForceAuthn'] = isset($request['_ForceAuthn']) && ($request['_ForceAuthn'] == 'true' || $request['_ForceAuthn'] == '1');
        $request['_IsPassive']  = isset($request['_IsPassive'])  && ($request['_IsPassive']  == 'true' || $request['_IsPassive']  == '1');
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

    /**
     * Decrypt an xml fragment.
     *
     * @param resource $privateKey OpenSSL private key for Corto to get the symmetric key.
     * @param array $element Array representation of an xml fragment
     * @param Bool $returnAsXML If true, the method returns an xml string.
     *                          If false (default), it returns an array
     *                          representation of the xml fragment.
     * @return String|Array The decrypted element (as an array or string
     *                      depending on the returnAsXml parameter.
     */
    protected function _decryptElement($privateKey, $element, $returnAsXML = false)
    {
        if (!isset($element['xenc:EncryptedData']['ds:KeyInfo']['xenc:EncryptedKey'][0]['xenc:CipherData'][0]['xenc:CipherValue'][0]['__v'])) {
            throw new EngineBlock_Corto_Module_Bindings_Exception("XML Encryption: No encrypted key found?");
        }
        if (!isset($element['xenc:EncryptedData']['xenc:CipherData'][0]['xenc:CipherValue'][0]['__v'])) {
            throw new EngineBlock_Corto_Module_Bindings_Exception("XML Encryption: No encrypted data found?");
        }
        $encryptedKey  = base64_decode($element['xenc:EncryptedData']['ds:KeyInfo']['xenc:EncryptedKey'][0]['xenc:CipherData'][0]['xenc:CipherValue'][0]['__v']);
        $encryptedData = base64_decode($element['xenc:EncryptedData']['xenc:CipherData'][0]['xenc:CipherValue'][0]['__v']);

        $sessionKey = null;
        if (!openssl_private_decrypt($encryptedKey, $sessionKey, $privateKey, OPENSSL_PKCS1_PADDING)) {
            throw new EngineBlock_Corto_Module_Bindings_Exception("XML Encryption: Unable to decrypt symmetric key using private key");
        }
        openssl_free_key($privateKey);

        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $ivSize = mcrypt_enc_get_iv_size($cipher);
        $iv = substr($encryptedData, 0, $ivSize);

        mcrypt_generic_init($cipher, $sessionKey, $iv);

        $decryptedData = mdecrypt_generic($cipher, substr($encryptedData, $ivSize));

        // Remove the CBC block padding
        $dataLen = strlen($decryptedData);
        $paddingLength = substr($decryptedData, $dataLen - 1, 1);
        $decryptedData = substr($decryptedData, 0, $dataLen - ord($paddingLength));

        mcrypt_generic_deinit($cipher);
        mcrypt_module_close($cipher);

        if ($returnAsXML) {
            return $decryptedData;
        }
        else {
            $newElement = EngineBlock_Corto_XmlToArray::xml2array($decryptedData);
            $newElement['__']['Raw'] = $decryptedData;
            return $newElement;
        }
    }

    protected function _verifySignature(array $message, $key, $requireMessageSigning = false)
    {
        if (isset($message['__']['Signature'])) { // We got a Signature in the URL (HTTP Redirect)
            return $this->_verifySignatureMessage($message, $key);
        }

        // Otherwise it's in the message or in the assertion in the message (HTTP Post Response)
        $messageIssuer = $message['saml:Issuer']['__v'];
        $publicKey = $this->_getRemoteEntityPublicKey($messageIssuer);
        $publicKeyFallbacks = $this->_getRemoteEntityFallbackPublicKeys($messageIssuer);

        if ($requireMessageSigning || isset($message['ds:Signature'])) {
            $messageVerified = $this->_verifySignatureXMLElement(
                $publicKey,
                $message['__']['Raw'],
                $message
            );
            if (!$messageVerified && !empty($publicKeyFallbacks)) {
                foreach ($publicKeyFallbacks as $publicKeyFallback) {
                    $messageVerified = $this->_verifySignatureXMLElement(
                        $publicKeyFallback,
                        $message['__']['Raw'],
                        $message
                    );
                    if ($messageVerified) {
                        break;
                    }
                }
            }
            if (!$messageVerified) {
                throw new EngineBlock_Corto_Module_Bindings_VerificationException("Invalid signature on message");
            }
        }

        if (!isset($message['saml:Assertion'])) {
            return true;
        }

        $assertionVerified = $this->_verifySignatureXMLElement(
            $publicKey,
            isset($message['saml:Assertion']['__']['Raw']) ? $message['saml:Assertion']['__']['Raw'] : $message['__']['Raw'],
            $message['saml:Assertion']
        );
        if (!$assertionVerified && !empty($publicKeyFallbacks)) {
            foreach ($publicKeyFallbacks as $publicKeyFallback) {
                $assertionVerified = $this->_verifySignatureXMLElement(
                    $publicKeyFallback,
                    isset($message['saml:Assertion']['__']['Raw']) ? $message['saml:Assertion']['__']['Raw'] : $message['__']['Raw'],
                    $message['saml:Assertion']
                );
                if ($assertionVerified) {
                    break;
                }
            }
        }

        if (!$assertionVerified) {
            throw new EngineBlock_Corto_Module_Bindings_VerificationException("Invalid signature on assertion");
        }

        return true;
    }

    protected function _verifySignatureMessage($message, $key)
    {
        $rawGet = $this->_server->getRawGet();

        $queryString = "$key=" . $rawGet[$key];
        if (isset($rawGet[$key])) {
            $queryString .= '&RelayState=' . $rawGet['RelayState'];
        }
        $queryString .= '&SigAlg=' . $rawGet['SigAlg'];

        $messageIssuer = $message['saml:Issuer']['__v'];
        $publicKey          = $this->_getRemoteEntityPublicKey($messageIssuer);
        $publicKeyFallbacks  = $this->_getRemoteEntityFallbackPublicKeys($messageIssuer);

        $verified = openssl_verify(
            $queryString,
            base64_decode($message['__']['Signature']),
            $publicKey
        );
        if (!$verified && !empty($publicKeyFallbacks)) {
            foreach ($publicKeyFallbacks as $publicKeyFallback) {
                $verified = openssl_verify(
                    $queryString,
                    base64_decode($message['__']['Signature']),
                    $publicKeyFallback
                );
                if ($verified) {
                    break;
                }
            }
        }

        if (!$verified) {
            throw new EngineBlock_Corto_Module_Bindings_VerificationException("Invalid signature on message");
        }

        return ($verified === 1);
    }

    protected function _verifySignatureXMLElement($publicKey, $xml, $element)
    {
        if (!isset($element['ds:Signature'])) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Element is not signed! " . $xml
            );
        }

        $document = new DOMDocument();
        $document->loadXML($xml);
        $xp = new DomXPath($document);
        $xp->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        if (count($element['ds:Signature']['ds:SignedInfo']['ds:Reference']) > 1) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Unsupported use of multiple Reference in a single Signature: " . $xml
            );
        }

        $reference = $element['ds:Signature']['ds:SignedInfo']['ds:Reference'][0];
        if (!in_array($reference['_URI'], array("", "#" . $element['_ID']))) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Unsupported use of URI Reference, should be empty or be XPointer to Signature parent id: " . $xml
            );
        }

        if (!isset($element['_ID']) || !$element['_ID']) {
            $log = $this->_server->getSessionLog();
            $log->attach($element, 'Signed element');

            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'Trying to verify signature on an element without an ID is not supported'
            );
        }

        $xpathResults = $xp->query("//*[@ID = '{$element['_ID']}']");
        if ($xpathResults->length === 0) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "URI Reference, not found? " . $xml
            );
        }
        if ($xpathResults->length > 1) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Multiple nodes found for URI Reference ID? " . $xml
            );
        }
        $referencedElement = $xpathResults->item(0);
        $referencedDocument  = new DomDocument();
        $importedNode = $referencedDocument->importNode($referencedElement->cloneNode(true), true);
        $referencedDocument->appendChild($importedNode);

        $referencedDocumentXml = $referencedDocument->saveXML();

        // First process any transforms
        if (isset($reference['ds:Transforms']['ds:Transform'])) {
            foreach ($reference['ds:Transforms']['ds:Transform'] as $transform) {
                switch ($transform['_Algorithm']) {
                    case 'http://www.w3.org/2000/09/xmldsig#enveloped-signature':
                        $transformDocument = new DOMDocument();
                        $transformDocument->loadXML($referencedDocumentXml);
                        $transformXpath = new DomXPath($transformDocument);
                        $transformXpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
                        $signature = $transformXpath->query(".//ds:Signature", $transformDocument)->item(0);
                        $signature->parentNode->removeChild($signature);
                        $referencedDocumentXml = $transformDocument->saveXML();
                        break;
                    case 'http://www.w3.org/2001/10/xml-exc-c14n#':
                        $nsPrefixes = array();
                        if (isset($transform['ec:InclusiveNamespaces']['_PrefixList'])) {
                            $nsPrefixes = explode(' ', $transform['ec:InclusiveNamespaces']['_PrefixList']);
                        }
                        $transformDocument = new DOMDocument();
                        $transformDocument->loadXML($referencedDocumentXml);
                        $referencedDocumentXml = $transformDocument->C14N(true, false, null, $nsPrefixes);
                        break;
                    default:
                        throw new EngineBlock_Corto_Module_Bindings_Exception(
                            "Unsupported transform " . $transform['_Algorithm'] . ' on XML: ' . $xml
                        );
                }
            }
        }

        // Verify the digest over the (transformed) element
        if ($reference['ds:DigestMethod']['_Algorithm'] !== 'http://www.w3.org/2000/09/xmldsig#sha1') {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Unsupported DigestMethod " . $reference['ds:DigestMethod']['_Algorithm'] . ' on XML: ' . $xml
            );
        }
        $ourDigest = sha1($referencedDocumentXml, TRUE);
        $theirDigest = base64_decode($reference['ds:DigestValue']['__v']);
        if ($ourDigest !== $theirDigest) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Digests do not match! on XML: " . $xml
            );
        }
        // Verify the signature over the SignedInfo (not over the entire document, only over the digest)

        $c14Algorithm = $element['ds:Signature']['ds:SignedInfo']['ds:CanonicalizationMethod']['_Algorithm'];
        if ($c14Algorithm !== 'http://www.w3.org/2001/10/xml-exc-c14n#') {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Unsupported CanonicalizationMethod '$c14Algorithm' on XML: $xml"
            );
        }
        if (!isset($element['ds:Signature']['ds:SignatureValue']['__v'])) {
            $this->_server->getSessionLog()->attach($element, 'Signed element');

            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'No sigurature value found on element?'
            );
        }

        $signatureAlgorithm = $element['ds:Signature']['ds:SignedInfo']['ds:SignatureMethod']['_Algorithm'];
        if ($signatureAlgorithm !== "http://www.w3.org/2000/09/xmldsig#rsa-sha1") {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Unsupported SignatureMethod '$signatureAlgorithm' on XML: $xml"
            );
        }

        // Find the signed element (like an assertion) in the global document (like a response)
        $signedInfoNodes = $xp->query("./ds:Signature/ds:SignedInfo", $referencedElement);
        if ((int)$signedInfoNodes->length === 0) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "No SignatureInfo found? On XML: $xml"
            );
        }
        if ($signedInfoNodes->length > 1) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "Multiple SignatureInfo nodes found? On XML: $xml"
            );
        }
        $signedInfoNode = $signedInfoNodes->item(0);
        $signedInfoXml = $signedInfoNode->C14N(true, false);

        $signatureValue = $element['ds:Signature']['ds:SignatureValue']['__v'];
        $signatureValue = base64_decode($signatureValue);

        return (openssl_verify($signedInfoXml, $signatureValue, $publicKey) == 1);
    }


    protected function _verifyInResponseTo($response)
    {
        if (!$response[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'InResponseTo']) {
            $message = "Unsollicited assertion (no InResponseTo in message) not supported!";
            throw new EngineBlock_Corto_Module_Bindings_Exception($message);
        }
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

    protected function _getRemoteEntityPublicKey($entityId)
    {
        return $this->_doGetRemoteEntityKey($entityId, 'public', true);
    }

    /**
     * Get a fallback public key, if one is known.
     *
     * @throws EngineBlock_Corto_Module_Bindings_Exception
     * @param $entityId
     * @return bool|resource
     */
    protected function _getRemoteEntityFallbackPublicKeys($entityId)
    {
        $keys = array();
        $types = array('public-fallback', 'public-fallback2');
        foreach ($types as $certType) {
            $publicKey = $this->_doGetRemoteEntityKey($entityId, $certType, false);
            if ($publicKey) {
                $keys[] = $publicKey;
            }
        }
        return $keys;
    }

    protected function _doGetRemoteEntityKey($entityId, $certificateType, $certificateRequired)
    {
        $remoteEntity = $this->_server->getRemoteEntity($entityId);

        if (!isset($remoteEntity['certificates'][$certificateType])) {
            if ($certificateRequired) {
                throw new EngineBlock_Corto_Module_Bindings_VerificationException("No $certificateType key known for $entityId");
            } else {
                return false;
            }
        }

        $publicKey = openssl_pkey_get_public($remoteEntity['certificates'][$certificateType]);
        if ($publicKey === false) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                "$certificateType key for $entityId is NOT a valid PEM SSL public key?!?! Value: " .
                $remoteEntity['certificates'][$certificateType],
                EngineBlock_Exception::CODE_WARNING
            );
        }

        return $publicKey;

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
     * @param $cortoIdpMetadata
     * @return array
     */
    protected function mapCortoIdpMetadataToSspIdpMetadata($cortoIdpMetadata)
    {
        $publicPems = array($cortoIdpMetadata['certificates']['public']);
        if (isset($cortoIdpMetadata['certificates']['public-fallback'])) {
            $publicPems[] = $cortoIdpMetadata['certificates']['public-fallback'];
        }
        if (isset($cortoIdpMetadata['certificates']['public-fallback2'])) {
            $publicPems[] = $cortoIdpMetadata['certificates']['public-fallback2'];
        }
        $publicPems = str_replace(
            array('-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n", "\t", " "),
            '',
            $publicPems
        );

        $config = array(
            'entityid'            => $cortoIdpMetadata['EntityID'],
            'SingleSignOnService' => $cortoIdpMetadata['SingleSignOnService']['Location'],
            'keys'                => array(),
        );
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
     * @param $sspResponse
     * @param $sspResponseStatus
     * @return array
     */
    protected function initCortoResponse(SAML2_Response $sspResponse)
    {
        $sspResponseStatus = $sspResponse->getStatus();
        $cortoResponse = array(
            '__t'           => 'samlp:Response',
            '_Destination'  => $sspResponse->getDestination(),
            '_ID'           => $sspResponse->getId(),
            '_InResponseTo' => $sspResponse->getInResponseTo(),
            '_IssueInstant' => $this->_server->timeStamp(0, $sspResponse->getIssueInstant()),
            '_Version'      => '2.0',
            'saml:Issuer'   =>
                array(
                    '_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity',
                    '__v'     => $sspResponse->getIssuer(),
                ),
            'samlp:Status'  =>
                array(
                    'samlp:StatusCode' => array(
                        '_Value' => $sspResponseStatus['Code'],
                    ),
                ),
        );
        if ($sspResponseStatus['Message']) {
            $cortoResponse['samlp:Status']['samlp:StatusMessage'] = array(
                '__v' => $sspResponseStatus['Message'],
            );
        }
        return $cortoResponse;
    }

    /**
     * @return SimpleSAML_Configuration
     */
    protected function getSspSpMetadata()
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

    /**
     * @param $assertion
     * @param $cortoResponse
     * @param $sspResponse
     * @return mixed
     */
    protected function mapSspResponseToCortoResponse($assertion, $cortoResponse, $sspResponse)
    {
        $nameId                          = $assertion->getNameId();
        $cortoResponse['saml:Assertion'] = array(
            '_ID'           => $assertion->getId(),
            '_IssueInstant' => $this->_server->timeStamp(0, $assertion->getIssueInstant()),
            '_Version'      => '2.0',
            'saml:Issuer'   => array(
                '_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:entity',
                '__v'     => $assertion->getIssuer(),
            ),
        );

        $subjectConfirmations                            = $assertion->getSubjectConfirmation();
        $subjectConfirmation                             = $subjectConfirmations[0];
        $cortoResponse['saml:Assertion']['saml:Subject'] = array(
            'saml:NameID'              => array(
                '_Format' => $nameId['Format'],
                '__v'     => $nameId['Value'],
            ),
            'saml:SubjectConfirmation' => array(
                '_Method'                      => $subjectConfirmation->Method,
                'saml:SubjectConfirmationData' => array(
                    '_Address'      => $subjectConfirmation->SubjectConfirmationData->Address,
                    '_InResponseTo' => $subjectConfirmation->SubjectConfirmationData->InResponseTo,
                    '_NotOnOrAfter' => $subjectConfirmation->SubjectConfirmationData->NotOnOrAfter,
                    '_Recipient'    => $subjectConfirmation->SubjectConfirmationData->Recipient,
                ),
            ),
        );

        $authnAuthorities                                       = $assertion->getAuthenticatingAuthority();
        $cortoResponse['saml:Assertion']['saml:AuthnStatement'] = array(
            '_AuthnInstant'     => $this->_server->timeStamp(0, $assertion->getAuthnInstant()),
            'saml:AuthnContext' =>
                array(
                    'saml:AuthnContextClassRef'    =>
                        array(
                            '__v' => $assertion->getAuthnContext(),
                        ),
                    'saml:AuthenticatingAuthority' =>
                        array(),
                ),
        );
        foreach ($authnAuthorities as $authnAuthority) {
            $cortoResponse['saml:Assertion']['saml:AuthnStatement']['saml:AuthnContext']['saml:AuthenticatingAuthority'][] = array(
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $authnAuthority,
            );
        }

        $attributes                                                 = $assertion->getAttributes();
        $cortoResponse['saml:Assertion']['saml:AttributeStatement'] = array(
            array(
                'saml:Attribute' => array(),
            ),
        );
        foreach ($attributes as $name => $values) {
            $attribute = array(
                '_Name'               => $name,
                'saml:AttributeValue' => array(),
            );
            foreach ($values as $value) {
                $attribute['saml:AttributeValue'][] = array(EngineBlock_Corto_XmlToArray::VALUE_PFX => $value);
            }
            $cortoResponse['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'][] = $attribute;
        }
        $cortoResponse['__'] = array(
            'ProtocolBinding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            'RelayState'      => $sspResponse->getRelayState(),
            'paramname'       => 'SAMLResponse',
        );
        return $cortoResponse;
    }
}
