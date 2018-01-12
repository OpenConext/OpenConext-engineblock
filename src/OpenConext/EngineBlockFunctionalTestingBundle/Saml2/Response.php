<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Saml2;

class Response extends \SAML2_Response
{
    /**
     * @var string
     */
    private $xml;

    public function setXml($xml)
    {
        $this->xml = $xml;

        return $xml;
    }

    public function toXml()
    {
        if (isset($this->xml)) {
            return $this->xml;
        }

        return $this->toSignedXML()->ownerDocument->saveXML();
    }
}
