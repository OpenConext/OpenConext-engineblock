<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Saml2;

use SAML2\Response as SAMLResponse;

class Response extends SAMLResponse
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
