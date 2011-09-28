<?php

namespace OpenSamlPhp\Request;
use OpenSamlPhp as general;

class Authn
    extends ARequest
    implements general\IMessage, general\ISignableMessage
{
    /**
     * @var int
     */
    private $_assertionConsumerServiceIndex;

    /**
     * @var int
     */
    private $_attributeConsumingServiceIndex;

    /**
     * @var string
     */
    private $_protocolBinding;

    /**
     * @var string
     */
    private $_providerName;

    /**
     * @var bool
     */
    private $_isPassive;

    /**
     * @var bool
     */
    private $_forceAuthn;

    public function __construct($id = null, $issueInstant = null)
    {
        parent::__construct($id, $issueInstant);
    }

    /**
     * @param int $assertionConsumerServiceIndex
     */
    public function setAssertionConsumerServiceIndex($assertionConsumerServiceIndex)
    {
        $this->_assertionConsumerServiceIndex = $assertionConsumerServiceIndex;
    }

    /**
     * @return int
     */
    public function getAssertionConsumerServiceIndex()
    {
        return $this->_assertionConsumerServiceIndex;
    }

    /**
     * @param int $attributeConsumingServiceIndex
     */
    public function setAttributeConsumingServiceIndex($attributeConsumingServiceIndex)
    {
        $this->_attributeConsumingServiceIndex = $attributeConsumingServiceIndex;
    }

    /**
     * @return int
     */
    public function getAttributeConsumingServiceIndex()
    {
        return $this->_attributeConsumingServiceIndex;
    }

    /**
     * @param boolean $forceAuthn
     */
    public function setForceAuthn($forceAuthn)
    {
        $this->_forceAuthn = $forceAuthn;
    }

    /**
     * @return boolean
     */
    public function getForceAuthn()
    {
        return $this->_forceAuthn;
    }

    /**
     * @param boolean $isPassive
     */
    public function setIsPassive($isPassive)
    {
        $this->_isPassive = $isPassive;
    }

    /**
     * @return boolean
     */
    public function getIsPassive()
    {
        return $this->_isPassive;
    }

    /**
     * @param string $protocolBinding
     */
    public function setProtocolBinding($protocolBinding)
    {
        $this->_protocolBinding = $protocolBinding;
    }

    /**
     * @return string
     */
    public function getProtocolBinding()
    {
        return $this->_protocolBinding;
    }

    /**
     * @param string $providerName
     */
    public function setProviderName($providerName)
    {
        $this->_providerName = $providerName;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->_providerName;
    }
}