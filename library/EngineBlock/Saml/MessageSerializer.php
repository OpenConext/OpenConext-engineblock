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
            $samlMessagetDomElement = $samlMessage->toSignedXML();
        } else {
            $samlMessagetDomElement = $samlMessage->toUnsignedXML();
        }
        return $samlMessagetDomElement->ownerDocument->saveXML($samlMessagetDomElement);
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