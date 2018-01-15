<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Saml2;

/**
 * Class AuthnRequest
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Saml2
 */
class AuthnRequest extends \SAML2_AuthnRequest
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

        return $this->toUnsignedXML()->ownerDocument->saveXML();
    }
}
