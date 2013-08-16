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

require_once ENGINEBLOCK_FOLDER_LIBRARY . 'simplesamlphp/lib/_autoload.php';

class DummySp_Controller_Index extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        if (!empty($_POST)) {
            $samlResponse = $this->factoryResponseFromHttpRequest($this->_getRequest());
            // @todo check status, for now it's assumed that a receiving a valid response means the user is logged in
            $_SESSION['loggedin'] = true;
        }

        if (empty($_SESSION['loggedin'])) {
            header('Location: ' . $this->getRedirectUrl());
            exit;
        }

        $this->setNoRender();
        header('Content-Type: text/html');
        die('<html><body><h1>DUMMY SP</h1></body></html>');
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        $engineUrl = 'https://engine-test.demo.openconext.org';

        $destinationUrl = $engineUrl . '/authentication/idp/single-sign-on';
        $assertionConsumerServiceURL = 'https://engine-test.demo.openconext.org/dummy-sp';
        $issuerUrl = 'https://engine-test.demo.openconext.org/dummy-sp';
        $samlPAuthNRequest = $this->factorySamlPAuthNRequest($destinationUrl, $assertionConsumerServiceURL, $issuerUrl);

        $message = $this->encodeSamlMessage($samlPAuthNRequest);
        return $destinationUrl . '?SAMLRequest=' . urlencode($message);
    }

    /**
     * @param $samlMessage
     * @return string
     */
    private function encodeSamlMessage($samlMessage)
    {
        return base64_encode(gzdeflate($samlMessage));
    }

    /**
     * @param string $destinationUrl
     * @param string $assertionConsumerServiceURL
     * @param string $issuerUrl
     * @return string
     */
    private function factorySamlPAuthNRequest(
        $destinationUrl,
        $assertionConsumerServiceURL,
        $issuerUrl
    )
    {
        $samlpAuthNRequest = new SAML2_AuthnRequest();
        $samlpAuthNRequest->setDestination($destinationUrl);
        $samlpAuthNRequest->setAssertionConsumerServiceURL($assertionConsumerServiceURL);
        $samlpAuthNRequest->setIssuer($issuerUrl);
        $samlpAuthNRequest->setProtocolBinding('urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST');
        $samlpAuthNRequest->setNameIdPolicy(array(
            'Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
            'AllowCreate' => true
        ));

        $samlpAuthNRequestDomElement = $samlpAuthNRequest->toUnsignedXML();
        return $samlpAuthNRequestDomElement->ownerDocument->saveXML($samlpAuthNRequestDomElement);
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return SAML2_Message
     */
    private function factoryResponseFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $samlResponseParameter = $this->getSAMLResponseParameterFromHttpRequest($httpRequest);
        $samlResponse = $this->decodeSAMLResponseParameter($samlResponseParameter);
        $responseDomElement = $this->responseToDomElement($samlResponse);
        return SAML2_Response::fromXML($responseDomElement);
    }

    /**
     * @param EngineBlock_Http_Request $httpRequest
     * @return string
     * @throws Exception
     */
    private function getSAMLResponseParameterFromHttpRequest(EngineBlock_Http_Request $httpRequest)
    {
        $samlResponseParameter = $httpRequest->getPostParameter('SAMLResponse');
        if (empty($samlResponseParameter)) {
            throw new Exception('No SAMLResponse');
        }

        return $samlResponseParameter;
    }

    /**
     * @param string $samlResponseParameter
     * @return string
     */
    private function decodeSAMLResponseParameter($samlResponseParameter)
    {
        return base64_decode($samlResponseParameter);
    }

    /**
     * @param string $samlResponse
     * @return DOMNode
     */
    private function responseToDomElement($samlResponse)
    {
        $document = new DOMDocument();
        $document->loadXML($samlResponse);
        return $document->getElementsByTagNameNs('urn:oasis:names:tc:SAML:2.0:protocol', 'Response')->item(0);
    }
}
