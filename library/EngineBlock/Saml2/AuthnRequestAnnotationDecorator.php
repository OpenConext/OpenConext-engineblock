<?php

use SAML2\AuthnRequest;

/**
 * @method getProxyCount()
 * @method getIsPassive()
 * @method getForceAuthn()
 * @method toUnsignedXML()
 */
class EngineBlock_Saml2_AuthnRequestAnnotationDecorator extends EngineBlock_Saml2_MessageAnnotationDecorator
{
    /**
     * @var AuthnRequest
     */
    protected $sspMessage;

    /**
     * @var string
     */
    protected $keyId;

    /**
     * @var bool
     */
    protected $wasSigned = false;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var bool
     */
    protected $unsolicited = false;

    /**
     * @var bool
     */
    protected $transparent = false;

    /**
     * @param AuthnRequest $request
     */
    public function __construct(AuthnRequest $request)
    {
        $this->sspMessage = $request;
    }

    /**
     * @return string[] EntityIds in Scoping > RequesterID element.
     */
    public function getRequesterIds()
    {
        return $this->sspMessage->getRequesterID();
    }

    public function setDebugRequest()
    {
        $this->debug = true;
        return $this;
    }

    public function isDebugRequest()
    {
        return $this->debug;
    }

    public function getKeyId()
    {
        return $this->keyId;
    }

    /**
     * @param $keyId
     * @return $this
     */
    public function setKeyId($keyId)
    {
        $this->keyId = $keyId;
        return $this;
    }

    public function setWasSigned()
    {
        $this->wasSigned = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function wasSigned()
    {
        return $this->wasSigned;
    }

    public function setUnsolicited()
    {
        $this->unsolicited = true;
        return $this;
    }

    public function isUnsolicited()
    {
        return $this->unsolicited;
    }

    public function setDebug()
    {
        $this->debug = true;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @return $this
     */
    public function setTransparent()
    {
        $this->transparent = true;
        return $this;
    }

    public function isTransparent()
    {
        return $this->transparent;
    }
}
