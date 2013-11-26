<?php
/**
 * NOTE: use for testing only!
 *
 * @todo write test
 */
class EngineBlock_Saml_MessageSerializer
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
        return $class::fromXml($messageDomElement);
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