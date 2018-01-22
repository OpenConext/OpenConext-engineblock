<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Saml2;

use SAML2\AuthnRequest as SAMLAuthnRequest;

/**
 * Class AuthnRequest
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Saml2
 */
class AuthnRequest extends SAMLAuthnRequest
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
