<?php
/**
 * NOTE: use for testing only!
 *
 * @todo write test
 */
class EngineBlock_Saml2_MessageSerializer
{
    /**
     * @param SAML2_Message $samlMessage
     * @return mixed
     */
    public function serialize(SAML2_Message $samlMessage)
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
     * @return SAML_Message
     */
    public function deserialize($samlMessageXml, $class)
    {
        $elementName = $this->getElementForClass($class);
        $document = new DOMDocument();
        $document->loadXML($samlMessageXml);
        $messageDomElement = $document->getElementsByTagNameNs('urn:oasis:names:tc:SAML:2.0:protocol', $elementName)->item(0);
        if ($class === 'SAML2_AuthnRequest') {
            return SAML2_AuthnRequest::fromXML($messageDomElement);
        }
        else if ($class === 'SAML2_Response') {
            return SAML2_Response::fromXML($messageDomElement);
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
            'SAML2_AuthnRequest' => 'AuthnRequest',
            'SAML2_Response' => 'Response'
        );

        if (!isset($mapping[$class])) {
            throw new Exception('Unknown Message Type' . $class);
        }

        return $mapping[$class];
    }
}
