<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use SAML2\AuthnRequest;
use SAML2\DOMDocumentFactory;
use SAML2\Message;
use SAML2\Response;

/**
 * NOTE: use for testing only!
 *
 * @todo write test
 */
class EngineBlock_Saml2_MessageSerializer
{
    /**
     * @param Message $samlMessage
     * @return mixed
     */
    public function serialize(Message $samlMessage)
    {
        if ($samlMessage->getSignatureKey()) {
            $samlMessageDomElement = $samlMessage->toSignedXML();
        } else {
            $samlMessageDomElement = $samlMessage->toUnsignedXML();
        }
        return $samlMessageDomElement->ownerDocument->saveXML($samlMessageDomElement);
    }

    /**
     * @param string $samlMessageXml
     * @param string $class
     * @return Message
     * @throws EngineBlock_Exception
     */
    public function deserialize($samlMessageXml, $class)
    {
        $elementName = $this->getElementForClass($class);
        $document = DOMDocumentFactory::fromString($samlMessageXml);
        $messageDomElement = $document->getElementsByTagNameNs('urn:oasis:names:tc:SAML:2.0:protocol', $elementName)->item(0);
        if ($class === AuthnRequest::class) {
            return AuthnRequest::fromXML($messageDomElement);
        }
        else if ($class === Response::class) {
            return Response::fromXML($messageDomElement);
        }

        throw new EngineBlock_Exception('Unknown message type for deserialization?');
    }

    /**
     * @param string $class
     * @return string
     * @throws Exception
     */
    private function getElementForClass($class)
    {
        $mapping = array(
            AuthnRequest::class => 'AuthnRequest',
            Response::class => 'Response'
        );

        if (!isset($mapping[$class])) {
            throw new Exception(sprintf('Unknown Message Type "%s"', $class));
        }

        return $mapping[$class];
    }
}
