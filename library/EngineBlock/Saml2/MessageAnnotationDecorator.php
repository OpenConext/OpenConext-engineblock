<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use SAML2\Message;
use SAML2\Request;
use SAML2\Response;

/**
 * @method getReturn()
 */
class EngineBlock_Saml2_MessageAnnotationDecorator
{
    const MESSAGE_TYPE_REQUEST = 'SAMLRequest';
    const MESSAGE_TYPE_RESPONSE = 'SAMLResponse';

    /**
     * @var Message
     */
    protected $sspMessage;

    /**
     * @var string
     */
    protected $deliverByBinding;

    /**
     * @param Message $message
     */
    function __construct(Message $message)
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
     * @return Message
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
        if ($this->sspMessage instanceof Request) {
            return self::MESSAGE_TYPE_REQUEST;
        }
        if ($this->sspMessage instanceof Response) {
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
