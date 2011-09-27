<?php

/**
 * @internal include the abstract baseclass
 */
require_once 'Abstract.php';

/**
 * Class for binding module specific exceptions.
 * @author Boy
 */
class Corto_Module_Bindings_Exception extends Corto_ProxyServer_Exception
{
}

/**
 * Class for binding module verification exceptions.
 * @author Boy
 */
class Corto_Module_Bindings_VerificationException extends Corto_Module_Bindings_Exception
{
}

class Corto_Module_Bindings_UnknownIssuerException extends Corto_Module_Bindings_VerificationException
{
}

class Corto_Module_Bindings_TimingException extends Corto_Module_Bindings_VerificationException
{
}

class Corto_Module_Bindings_UnableToReceiveMessageException extends Corto_Module_Bindings_Exception
{
}

/**
 * The bindings module for Corto, which implements support for various data
 * bindings.
 * @author Boy
 */
class Corto_Module_Bindings extends Corto_Module_Abstract
{
    const ARTIFACT_BINARY_FORMAT = 'ntypecode/nendpointindex/H40sourceid/H40messagehandle';

    const KEY_ARTIFACT = 'SAMLArt';
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
            //'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact'        => 'sendHTTPArtifact',
            //'urn:oasis:names:tc:SAML:2.0:bindings:URI'                  => 'sendURI',
            //'urn:oasis:names:tc:SAML:2.0:bindings:SOAP'                 => 'sendSOAP',
            'INTERNAL'                                                  => 'sendInternal',
            'JSON-Redirect'                                             => '_sendHTTPRedirect',
            'JSON-POST'                                                 => '_sendHTTPPost',
            null                                                        => '_sendHTTPRedirect',

            //'urn:oasis:names:tc:SAML:1.0:profiles:browser-post'         => 'sendbrowserpost',
            //'urn:oasis:names:tc:SAML:1.0:profiles:browser-artifact-01'  => 'sendbrowserartifact01',
            //'urn:oasis:names:tc:SAML:1.0:bindings:SOAP-binding'         => 'xxxx',
            //'urn:mace:shibboleth:1.0:profiles:AuthnRequest'             => 'sendShibAuthnRequest',
    );

    /**
     * Process an incoming SAML request message. The data is retrieved automatically 
     * depending on the binding used.
     */
    public function receiveRequest()
    {
        $request = $this->_receiveMessage(self::KEY_REQUEST);
        $this->_server->getSessionLog()->debug("Received request: " . var_export($request, true));

        $this->_verifyRequest($request);
        $this->_c14nRequest($request);

        return $request;
    }

    /**
     * Process an incoming SAML response message.
     */
    public function receiveResponse()
    {
        $response = $this->_receiveMessage(self::KEY_RESPONSE);
        $this->_server->getSessionLog()->debug("Received response: " . var_export($response, true));
        if (isset($response[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['Binding']) &&
            $response[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['Binding'] === "INTERNAL") {
            return $response;
        }

        $this->_decryptResponse($response);
        $this->_verifyResponse($response);
        
        return $response;
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
            return $message;
        }

        $message = $this->_receiveMessageFromArtifact($key);
        if (!empty($message)) {
            return $message;
        }

        $message = $this->_receiveMessageFromHttpPost($key);
        if (!empty($message)) {
            return $message;
        }

        $message = $this->_receiveMessageFromHttpRedirect($key);
        if (!empty($message)) {
            return $message;
        }

        throw new Corto_Module_Bindings_UnableToReceiveMessageException('Unable to receive message: ' . $key);
    }

    protected function _receiveMessageFromInternalBinding($key)
    {
        if (!isset($_REQUEST[$key]) || !is_array($_REQUEST[$key])) {
            return false;
        }
        
        $message = $_REQUEST[$key];
        $message[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['Binding'] = "INTERNAL";
        return $message;
    }

    /**
     * Retrieve a message via artifact binding.
     * @param String $key The key to look for.
     * @return mixed False if there was no suitable message in this binding
     *               String the message if it was found
     *               An exception if something went wrong.
     */
    protected function _receiveMessageFromArtifact($key)
    {
        if (!isset($_REQUEST[self::KEY_ARTIFACT])) {
            return false;
        }

        $artifacts = base64_decode($_REQUEST[self::KEY_ARTIFACT]);
        $artifacts = unpack(self::ARTIFACT_BINARY_FORMAT, $artifacts);

        switch ($key) {
            case self::KEY_REQUEST:
                // Trying to get an artifact from an SP, identify ourselves as an idp
                $issuer = $this->_server->getCurrentEntityUrl('idPMetadataService');
                break;
            case self::KEY_RESPONSE:
                // Trying to get an artifact from an IdP, identify ourselves as a sp
                $issuer = $this->_server->getCurrentEntityUrl('sPMetadataService');
                break;
            default:
                throw new Corto_Module_Bindings_Exception("Unknown message type '$key'");
        }

        $artifactResolveMessage = array(
            'samlp:ArtifactResolve' => array(
                '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
                '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
                '_ID'           => $this->_server->getNewId(),
                '_Version'      => '2.0',
                '_IssueInstant' => $this->_server->timeStamp(),

                'saml:Artifact' => array('__v' => $_REQUEST['SAMLArt']),
                'saml:Issuer'   => array('__v' => $issuer),
            ),
        );

        if (!isset($artifacts['sourceid'])) {
            throw new Corto_Module_Bindings_Exception("No Source ID found in SAML2 Artifact?!");
        }

        $sourceEntity = $this->_server->getRemoteEntity($artifacts['sourceid']);
        if (!isset($sourceEntity['ArtifactResolutionServiceLocation'])) {
            throw new Corto_Module_Bindings_Exception("Entity {$artifacts['sourceid']} mentioned in SAML2 Artifact found, but no Artifact Resolution Service is registered");
        }

        $artifactResponse = $this->_soapRequest(
            $sourceEntity['ArtifactResolutionServiceLocation'],
            $artifactResolveMessage
        );

        if ($key === self::KEY_REQUEST) {
            if (isset($artifactResponse['samlp:ArtifactResponse']['samlp:AuthnRequest'])) {
                $message = $artifactResponse['samlp:ArtifactResponse']['samlp:AuthnRequest'];
                $message[Corto_XmlToArray::TAG_NAME_KEY] = 'samlp:AuthnRequest';
            }
            else {
                return false;
            }
        }
        else if ($key === self::KEY_RESPONSE) {
            if (isset($artifactResponse['samlp:ArtifactResponse']['samlp:AuthnRequest'])) {
                $message = $artifactResponse['samlp:ArtifactResponse']['samlp:AuthnRequest'];
                $message[Corto_XmlToArray::TAG_NAME_KEY] = 'samlp:Response';
            }
            else {
                return false;
            }
        }

        $relayState = $_REQUEST['RelayState'];
        $message[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['RelayState'] = $relayState;

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
        
        $relayState     = $_POST['RelayState'];
        $messageArray[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['RelayState']   = $relayState;
        $messageArray[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['Raw']          = $message;
        $messageArray[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['paramname']    = $key;
        if (isset($_POST['return'])) {
            $messageArray[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['Return'] = $_POST['return']; 
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
            throw new Corto_Module_Bindings_UnableToReceiveMessageException("Message not base64 encoded!");
        }

        $message = @gzinflate($message);
        if (!$message) {
            throw new Corto_Module_Bindings_UnableToReceiveMessageException("Message not gzipped!");
        }
        
        $messageArray = $this->_getArrayFromReceivedMessage($message);

        if (isset($_GET['RelayState'])) {
            $relayState         = $_GET['RelayState'];
            $messageArray[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['RelayState'] = $relayState;
        }

        if (isset($_GET['Signature'])) {
            $messageArray[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['Signature']        = $_GET['Signature'];
            $messageArray[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['SigningAlgorithm'] = $_GET['SigAlg'];
        }

        $messageArray[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['Raw'] = $message;
        $messageArray[Corto_XmlToArray::PRIVATE_KEY_PREFIX]['paramname'] = $key;

        return $messageArray;
    }

    /**
     * Decode a JSON or XML encoded message into a PHP array. It uses a crude
     * detection to see whether it's dealing with json (if the message starts 
     * with '{') or xml (all other cases). 
     * @param String $message A Json or XML encoded message
     * @return array An array of data that was contained in the message
     */
    protected function _getArrayFromReceivedMessage($message)
    {
        if (substr($message, 0, 1) == '{') {
            return json_decode($message, true);
        }

        return Corto_XmlToArray::xml2array($message);
    }

    /**
     * Verify if a request has a valid signature (if required), whether
     * the issuer is a known entity and whether the message is destined for
     * us. Throws an exception if any of these conditions are not met.
     * @param array $request The array with request data
     * @throws Corto_Module_Bindings_VerificationException if any of the
     * verifications fail
     */
    protected function _verifyRequest(array &$request)
    {
        $remoteEntity = $this->_verifyKnownIssuer($request);
        if ((isset($remoteEntity['AuthnRequestsSigned']) && $remoteEntity['AuthnRequestsSigned']) ||
            ($this->_server->getCurrentEntitySetting('WantsAuthnRequestsSigned', false))) {
            $this->_verifySignature($request, self::KEY_REQUEST);
            $request['__']['WasSigned'] = true;
        }
        
        $this->_verifyMessageDestinedForUs($request);
    }

    /**
     * Verify if a message has an issuer that is known to us. If not, it 
     * throws a Corto_Module_Bindings_VerificationException.
     * @param array $message
     * @throws Corto_Module_Bindings_VerificationException
     */
    protected function _verifyKnownIssuer(array $message)
    {
        $messageIssuer = $message['saml:Issuer']['__v'];
        try {
            $remoteEntity = $this->_server->getRemoteEntity($messageIssuer);
        } catch (Corto_ProxyServer_Exception $e) {
            throw new Corto_Module_Bindings_UnknownIssuerException(
                "Issuer '{$messageIssuer}' is not a known remote entity? (please add SP/IdP to Remote Entities)"
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
     * @param array $element An array representation of an xml fragment 
     * @param unknown_type $tag ???
     * @return array The encrypted version of the element.
     */
    protected function _encryptElement($publicKey, $element, $tag = null)
    {
        if ($tag) {
            $element['__t'] = $tag;
        }
        $data = Corto_XmlToArray::array2xml($element);
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
     * Decrypt a response message
     * @param array $response The response to decrypt.
     */
    protected function _decryptResponse(array &$response)
    {
        if (isset($response['saml:EncryptedAssertion'])) {
            $encryptedAssertion = $response['saml:EncryptedAssertion'];

            $response['saml:Assertion'] = $this->_decryptElement(
                $this->_getCurrentEntityPrivateKey(),
                $encryptedAssertion
            );
        }
    }

    /**
     * Decrypt an xml fragment.
     * @param String $privateKey The private key to use to decrypt the 
     *                           elements.
     * @param array $element Array representation of an xml fragment
     * @param Bool $returnAsXML If true, the method returns an xml string.
     *                          If false (default), it returns an array 
     *                          representation of the xml fragment.
     * @return String|Array The decrypted element (as an array or string 
     *                      depending on the returnAsXml parameter.
     */
    protected function _decryptElement($privateKey, $element, $returnAsXML = false)
    {
        $encryptedKey  = base64_decode($element['xenc:EncryptedData']['ds:KeyInfo']['xenc:EncryptedKey']['xenc:CipherData']['xenc:CipherValue']['__v']);
        $encryptedData = base64_decode($element['xenc:EncryptedData']['xenc:CipherData']['xenc:CipherValue']['__v']);

        openssl_private_decrypt($encryptedKey, $sessionKey, $privateKey, OPENSSL_PKCS1_PADDING);
        openssl_free_key($privateKey);

        $cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $ivSize = mcrypt_enc_get_iv_size($cipher);
        $iv = substr($encryptedData, 0, $ivSize);

        mcrypt_generic_init($cipher, $sessionKey, $iv);

        $decryptedData = mdecrypt_generic($cipher, substr($encryptedData, $ivSize));
        mcrypt_generic_deinit($cipher);
        mcrypt_module_close($cipher);

        if ($returnAsXML) {
            return $decryptedData;
        }
        return Corto_XmlToArray::xml2array($decryptedData);
    }

    protected function _verifyResponse(array &$response)
    {
        $this->_verifyKnownIssuer($response);
        if ($this->_server->getCurrentEntitySetting('WantsAssertionsSigned', false)) {
            $this->_verifySignature($response, self::KEY_RESPONSE);
            $request['__']['WasSigned'] = true;
        }
        $this->_verifyMessageDestinedForUs($response);
        $this->_verifyTimings($response);
    }

    protected function _verifySignature(array $message, $key)
    {
        if (isset($message['__']['Signature'])) { // We got a Signature in the URL (HTTP Redirect)
            return $this->_verifySignatureMessage($message, $key);
        }

        // Otherwise it's in the message or in the assertion in the message (HTTP Post Response)
        $messageIssuer = $message['saml:Issuer']['__v'];
        $publicKey = $this->_getRemoteEntityPublicKey($messageIssuer);
        $publicKeyFallback = $this->_getRemoteEntityFallbackPublicKey($messageIssuer);

        if (isset($message['ds:Signature'])) {
            $messageVerified = $this->_verifySignatureXMLElement(
                $publicKey,
                $message['__']['Raw'],
                $message
            );
            if (!$messageVerified && $publicKeyFallback) {
                $messageVerified = $this->_verifySignatureXMLElement(
                    $publicKeyFallback,
                    $message['__']['Raw'],
                    $message
                );
            }
            if (!$messageVerified) {
                throw new Corto_Module_Bindings_VerificationException("Invalid signature on message");
            }
        }

        if (!isset($message['saml:Assertion'])) {
            return true;
        }

        $assertionVerified = $this->_verifySignatureXMLElement(
            $publicKey,
            $message['__']['Raw'],
            $message['saml:Assertion']
        );
        if (!$assertionVerified && $publicKeyFallback) {
            $assertionVerified = $this->_verifySignatureXMLElement(
                $publicKeyFallback,
                $message['__']['Raw'],
                $message['saml:Assertion']
            );
        }

        if (!$assertionVerified) {
            throw new Corto_Module_Bindings_VerificationException("Invalid signature on assertion");
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
        $publicKeyFallback  = $this->_getRemoteEntityFallbackPublicKey($messageIssuer);

        $verified = openssl_verify(
            $queryString,
            base64_decode($message['__']['Signature']),
            $publicKey
        );
        if (!$verified && $publicKeyFallback) {
            $verified = openssl_verify(
                $queryString,
                base64_decode($message['__']['Signature']),
                $publicKeyFallback
            );
        }
        
        if (!$verified) {
            throw new Corto_Module_Bindings_VerificationException("Invalid signature for message");
        }

        return ($verified === 1);
    }
    

    protected function _verifySignatureXMLElement($publicKey, $xml, $element)
    {
        $signatureValue = base64_decode($element['ds:Signature']['ds:SignatureValue']['__v']);
        $digestValue    = base64_decode($element['ds:Signature']['ds:SignedInfo']['ds:Reference'][0]['ds:DigestValue']['__v']);

        $document = new DOMDocument;
        $document->loadXML($xml);
        $xp = new DomXPath($document);
        $xp->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        $id = $element['_ID'];
        $signedElement  = $xp->query("//*[@ID = '$id']")->item(0);
        $signature      = $xp->query(".//ds:Signature", $signedElement)->item(0);
        $signedInfo     = $xp->query(".//ds:SignedInfo", $signature)->item(0)->C14N(true, false);
        $signature->parentNode->removeChild($signature);
        $canonicalXml = $signedElement->C14N(true, false);

        $digestMatches = (sha1($canonicalXml, TRUE) == $digestValue);
        if (!$digestMatches) {
            return false;
        }
        return (openssl_verify($signedInfo, $signatureValue, $publicKey) == 1);
    }

    protected function _verifyMessageDestinedForUs(array $message)
    {
        $destinationId = $message['_Destination'];
        if ($destinationId && $this->_verifyDestination) { // Destination is optional
            if ($this->_server->selfDestination() != $destinationId) {
                throw new Corto_Module_Bindings_VerificationException("Destination: '$destinationId' is not here; message not destined for us");
            }
        }
    }

    protected function _verifyTimings(array $message)
    {
        // just use string cmp all times in ISO like format without timezone (but everybody appends a Z anyways ...)
        $skew = $this->_server->getConfig('max_age_seconds', 3600);
        $aShortWhileAgo = $this->_server->timeStamp(-$skew);
        $inAShortWhile  = $this->_server->timeStamp($skew);
        $issues = array();

        // Check SAMLResponse SubjectConfirmation timings

        if (isset($message['saml:Assertion']['saml:Subject']['saml:SubjectConfirmation']['saml:SubjectConfirmationData']['_NotOnOrAfter'])) {
            if ($aShortWhileAgo > $message['saml:Assertion']['saml:Subject']['saml:SubjectConfirmation']['saml:SubjectConfirmationData']['_NotOnOrAfter']) {
                $issues[] = 'SubjectConfirmation too old';
            }
        }

        // Check SAMLResponse Conditions timings

        if (isset($message['saml:Assertion']['saml:Conditions']['_NotBefore'])) {
            if ($inAShortWhile < $message['saml:Assertion']['saml:Conditions']['_NotBefore']) {
                $issues[] = 'Assertion Conditions not valid yet';
            }
        }

        if (isset($message['saml:Assertion']['saml:Conditions']['_NotOnOrAfter'])) {
            if ($aShortWhileAgo > $message['saml:Assertion']['saml:Conditions']['_NotOnOrAfter']) {
                $issues[] = 'Assertions Condition too old';
            }
        }

        // Check SAMLResponse AuthnStatement timing

        if (isset($message['saml:Assertion']['saml:AuthnStatement']['_SessionNotOnOrAfter'])) {
            if ($aShortWhileAgo > $message['saml:Assertion']['saml:AuthnStatement']['_SessionNotOnOrAfter']) {
                $issues[] = 'AuthnStatement Session too old';
            }
        }

        if (!empty($issues)) {
            $message = 'Problems detected with timings! Please check if your server has the correct time set.';
            $message .= ' Issues: '.implode(PHP_EOL, $issues);
            throw new Corto_Module_Bindings_TimingException($message);
        }
        return true;
    }

    protected function _soapRequest($soapServiceUrl, array $body)
    {
        $soapEnvelope = array(
            '__t' => 'SOAP-ENV:Envelope',
            '_xmlns:SOAP-ENV' => "http://schemas.xmlsoap.org/soap/envelope/",
            'SOAP-ENV:Body' => $body,
        );

        $curlOptions = array(
            CURLOPT_URL             => $soapServiceUrl,
            CURLOPT_HTTPHEADER      => array('SOAPAction: ""'),
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_SSL_VERIFYPEER  => FALSE,
            CURLOPT_POSTFIELDS      => Corto_XmlToArray::array2xml($soapEnvelope),
            CURLOPT_HEADER          => 0,
        );

        $curlHandler = curl_init();
        curl_setopt_array($curlHandler, $curlOptions);
        $curlResult = curl_exec($curlHandler);

        $soapResponse = Corto_XmlToArray::xml2array($curlResult);

        return $soapResponse['SOAP-ENV:Body'];
    }

    public function soapResponse(array $body)
    {
        $soapResponse = array(
            '__t'               => 'SOAP-ENV:Envelope',
            'xmlns:SOAP-ENV'    => "http://schemas.xmlsoap.org/soap/envelope/",
            'SOAP-ENV:Body'     => $body,
        );
        $xml = Corto_XmlToArray::array2xml($soapResponse);

        $this->_server->sendHeader('Content-Type', 'application/xml');
        $this->_server->sendOutput($xml);
    }

    public function send(array $message, array $remoteEntity)
    {
        $bindingUrn = $message['__']['ProtocolBinding'];

        if (!isset($this->_bindings[$bindingUrn])) {
            throw new Corto_Module_Bindings_Exception('Unknown binding: '. $bindingUrn);
        }
        $function = $this->_bindings[$bindingUrn];
        
        $this->$function($message, $remoteEntity);
    }

    protected function _sendHTTPRedirect(array $message, $remoteEntity)
    {
        $messageType = $message['__']['paramname'];

        // Determine if we should sign the message
        $wantRequestsSigned = ($remoteEntity['AuthnRequestsSigned'] ||
                                $this->_server->getCurrentEntitySetting('AuthnRequestsSigned'));
        $mustSign = ($messageType===self::KEY_REQUEST && $wantRequestsSigned);
        if ($mustSign) {
            $this->_server->getSessionLog()->debug("HTTP-Redirect: Removing signature");
            unset($message['ds:Signature']);
        }

        // Encode the message in destination format
        if ($message['__']['ProtocolBinding'] == 'JSON-Redirect') {
            $encodedMessage = json_encode($message);
        }
        else {
            $encodedMessage = Corto_XmlToArray::array2xml($message);
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
        $queryString .= $message['__']['RelayState'] ? '&RelayState=' . urlencode($message['__']['RelayState']) : "";

        // Sign the message
        if (isset($remoteEntity['SharedKey'])) {
            $queryString .= "&Signature=" . urlencode(base64_encode(sha1($remoteEntity['SharedKey'] . sha1($queryString))));
        } elseif ($mustSign) {
            $this->_server->getSessionLog()->debug("HTTP-Redirect: (Re-)Signing");
            $queryString .= '&SigAlg=' . urlencode($this->_server->getConfig('SigningAlgorithm'));

            $key = $this->_getCurrentEntityPrivateKey();

            $signature = "";
            openssl_sign($queryString, $signature, $key);
            openssl_free_key($key);

            $queryString .= '&Signature=' . urlencode(base64_encode($signature));
        }

        // Build the full URL
        $location = $message['_Destination'] . $message['_Recipient']; # shib remember ...
        $location .= "?" . $queryString;

        // Redirect
        $this->_server->redirect($location, $message);
    }

    protected function _getCurrentEntityPrivateKey()
    {
        $certificates = $this->_server->getCurrentEntitySetting('certificates', array());
        if (!isset($certificates['private'])) {
            throw new Corto_Module_Bindings_Exception('Current entity has no private key, unable to sign message! Please set ["certificates"]["private"]!');
        }
        $key = openssl_pkey_get_private($certificates['private']);
        if ($key === false) {
            throw new Corto_Module_Bindings_Exception("Current entity ['certificates']['private'] value is NOT a valid PEM formatted SSL private key?!? Value: " . $certificates['private']);
        }
        return $key;
    }

    protected function _getRemoteEntityPublicKey($entityId)
    {
        $remoteEntity = $this->_server->getRemoteEntity($entityId);

        if (!isset($remoteEntity['certificates']['public'])) {
            throw new Corto_Module_Bindings_VerificationException("No public key known for $entityId");
        }

        $publicKey = openssl_pkey_get_public($remoteEntity['certificates']['public']);
        if ($publicKey === false) {
            throw new Corto_Module_Bindings_Exception(
                "Public key for $entityId is NOT a valid PEM SSL public key?!?! Value: " .
                $remoteEntity['certificates']['public']
            );
        }

        return $publicKey;
    }

    /**
     * Get a fallback public key, if one is known.
     *
     * @throws Corto_Module_Bindings_Exception
     * @param $entityId
     * @return bool|resource
     */
    protected function _getRemoteEntityFallbackPublicKey($entityId)
    {
        $remoteEntity = $this->_server->getRemoteEntity($entityId);

        if (!isset($remoteEntity['certificates']['public-fallback'])) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($remoteEntity['certificates']['public-fallback']);
        if ($publicKey === false) {
            throw new Corto_Module_Bindings_Exception(
                "Public key for $entityId is NOT a valid PEM SSL public key?!?! Value: " .
                $remoteEntity['certificates']['public']
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
            if ($name == 'SAMLRequest' && ($remoteEntity['WantsAuthnRequestsSigned'] || $this->_server->getCurrentEntitySetting('AuthnRequestsSigned'))) {
                $this->_server->getSessionLog()->debug("HTTP-Redirect: (Re-)Signing");
                $message = $this->_server->sign($message);
            }
            else if ($name == 'SAMLResponse' && isset($remoteEntity['WantsAssertionsSigned']) && $remoteEntity['WantsAssertionsSigned']) {
                $this->_server->getSessionLog()->debug("HTTP-Redirect: (Re-)Signing Assertion");

                $message['saml:Assertion']['__t'] = 'saml:Assertion';
                $message['saml:Assertion']['_xmlns:saml'] = "urn:oasis:names:tc:SAML:2.0:assertion";
                $message['saml:Assertion']['ds:Signature'] = '__placeholder__';

                uksort($message['saml:Assertion'], array(__CLASS__, '_usortAssertion'));

                $message['saml:Assertion'] = $this->_server->sign($message['saml:Assertion']);
                #$enc = docrypt(certs::$server_crt, $message['saml:Assertion'], 'saml:EncryptedAssertion');

            }
            else if ($name == 'SAMLResponse' && isset($remoteEntity['WantsResponsesSigned']) && $remoteEntity['WantsResponsesSigned']) {
                $this->_server->getSessionLog()->debug("HTTP-Redirect: (Re-)Signing");

                uksort($message['saml:Assertion'], array(__CLASS__, '_usortAssertion'));

                $message = $this->_server->sign($message);
            }

            $encodedMessage = Corto_XmlToArray::array2xml($message);

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
        $this->_server->getSessionLog()->debug("HTTP-Post: Sending Message: " . var_export($message, true));
        $output = $this->_server->renderTemplate(
            'form',
            array(
                'action' => $action,
                'message' => $encodedMessage,
                'xtra' => $extra,
                'name' => $name,
                'trace' => $this->_server->getConfig('debug', false) ? htmlentities(Corto_XmlToArray::formatXml(Corto_XmlToArray::array2xml($message))) : '',
        ));
        $this->_server->sendOutput($output);
    }

    public function sendInternal($message, $remoteEntity)
    {
        // Store the message
        $name            = $message['__']['paramname'];
        $_REQUEST[$name] = $message;

        $destinationLocation = $message['_Destination'];
        $parameters = $this->_server->getParametersFromUrl($destinationLocation);
        $this->_server->setCurrentEntity($parameters['EntityCode'], $parameters['RemoteIdPMd5']);

        $this->_server->getSessionLog()->debug("Using internal binding for destination: $destinationLocation, resulting in parameters: " . var_export($parameters, true));

        $serviceName = $parameters['ServiceName'];

        $this->_server->getSessionLog()->debug("Calling service '$serviceName'");
        $this->_server->getServicesModule()->$serviceName();
        $this->_server->getSessionLog()->debug("Done calling service '$serviceName'");
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

        $canonicalXml = DOMDocument::loadXML(Corto_XmlToArray::array2xml($element))->firstChild->C14N(true, false);

        $signature['ds:SignedInfo']['ds:Reference'][0]['ds:DigestValue']['__v'] = base64_encode(sha1($canonicalXml, TRUE));
        $signature['ds:SignedInfo']['ds:Reference'][0]['_URI'] = "#" . $element['_ID'];

        $canonicalXml2 = DOMDocument::loadXML(Corto_XmlToArray::array2xml($signature['ds:SignedInfo']))->firstChild->C14N(true, false);

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
        $result = self::_usortByPrefix(Corto_XmlToArray::PRIVATE_KEY_PREFIX, $a, $b);
        if ($result !== false) {
            return $result;
        }

        $result = self::_usortByPrefix(Corto_XmlToArray::TAG_NAME_KEY, $a, $b);
        if ($result !== false) {
            return $result;
        }

        $result = self::_usortByPrefix('_xmlns', $a, $b);
        if ($result !== false) {
            return $result;
        }

        $result = self::_usortByPrefix(Corto_XmlToArray::ATTRIBUTE_KEY_PREFIX, $a, $b);
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
}
