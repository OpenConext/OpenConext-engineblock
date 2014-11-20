<?php

class EngineBlock_Saml2_MessageAnnotationDecorator
{
    const MESSAGE_TYPE_REQUEST = 'SAMLRequest';
    const MESSAGE_TYPE_RESPONSE = 'SAMLResponse';

    /**
     * @var SAML2_Message
     */
    protected $sspMessage;

    /**
     * @var string
     */
    protected $deliverByBinding;

    /**
     * @param SAML2_Message $message
     */
    function __construct(SAML2_Message $message)
    {
        $this->sspMessage;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    function __call($name, $arguments)
    {
        return call_user_func_array(array($this->sspMessage, $name), $arguments);
    }

    #region proxy methods

    /**
     * Get message ID
     * @return string
     */
    public function getId()
    {
        return $this->sspMessage->getId();
    }

    /**
     * Get the message Issuer.
     *
     * @return NULL|string
     */
    public function getIssuer()
    {
        return $this->sspMessage->getIssuer();
    }

    /**
     * Get the message Destination
     *
     * @return NULL|string
     */
    public function getDestination()
    {
        return $this->sspMessage->getDestination();
    }

    #endregion proxy methods

    /**
     * @return \SAML2_Message
     */
    public function getSspMessage()
    {
        return $this->sspMessage;
    }

    /**
     * @param string $deliverByBinding
     */
    public function setDeliverByBinding($deliverByBinding)
    {
        $this->deliverByBinding = $deliverByBinding;
    }

    /**
     * @return string
     */
    public function getDeliverByBinding()
    {
        return $this->deliverByBinding;
    }

    /**
     * @return string
     * @throws RuntimeException
     */
    public function getMessageType()
    {
        if ($this->sspMessage instanceof SAML2_Request) {
            return self::MESSAGE_TYPE_REQUEST;
        }
        if ($this->sspMessage instanceof SAML2_Response) {
            return self::MESSAGE_TYPE_RESPONSE;
        }
        throw new \RuntimeException('Unknown message type?!');
    }

    /**
     * Dump a string representation of this annotated message used for debugging.
     *
     * @return string
     */
    public function __toString()
    {
        $vars = get_object_vars($this);
        $vars['sspMessage'] = $this->sspMessage->toUnsignedXML()->ownerDocument->saveXML();
        return json_encode($vars);
    }
}
