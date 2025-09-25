<?php

/**
 * Copyright 2010 SURFnet B.V.
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

use OpenConext\EngineBlock\Metadata\Loa;
use SAML2\AuthnRequest;
use SAML2\DOMDocumentFactory;

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
     * @var string|null Temporary storage for serialized XML
     */
    private ?string $_serializableSspMessageXml = null;

    /**
     * @var string|null Persisted RelayState while serialized (not part of the AuthnRequest XML itself)
     */
    private ?string $_serializableRelayState = null;

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

    /**
     * @return array|null
     */
    public function getRequestedAuthnContext()
    {
        return $this->sspMessage->getRequestedAuthnContext();
    }

    /**
     * @return Loa[]
     * @param Loa[]
     */
    public function getStepupObligations(array $stepUpLoas)
    {
        $requestedAuthncontext = $this->sspMessage->getRequestedAuthnContext();
        $obligations = [];
        if ($requestedAuthncontext && $requestedAuthncontext['AuthnContextClassRef']) {
            foreach ($requestedAuthncontext['AuthnContextClassRef'] as $rac) {
                foreach($stepUpLoas as $loa) {
                    if ($loa->getIdentifier() === $rac){
                        $obligations[] = $loa;
                    }
                }
            }
        }
        return $obligations;
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

    public function setForceAuthn(bool $isForceAuthn)
    {
        $this->sspMessage->setForceAuthn($isForceAuthn);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        if ($this->sspMessage instanceof AuthnRequest) {
            $this->_serializableSspMessageXml = $this->sspMessage->toUnsignedXML()->ownerDocument->saveXML();
            $this->_serializableRelayState = $this->sspMessage->getRelayState();
        }

        return ['keyId', 'wasSigned', 'debug', 'unsolicited', 'transparent', '_serializableSspMessageXml', '_serializableRelayState'];
    }

    public function __wakeup()
    {
        if (isset($this->_serializableSspMessageXml)) {
            $document = DOMDocumentFactory::fromString($this->_serializableSspMessageXml);
            $messageDomElement = $document->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:protocol', 'AuthnRequest')->item(0);

            if ($messageDomElement) {
                $this->sspMessage = AuthnRequest::fromXML($messageDomElement);
                if (isset($this->_serializableRelayState) && $this->_serializableRelayState !== null) {
                    $this->sspMessage->setRelayState($this->_serializableRelayState);
                }
            }

            unset($this->_serializableSspMessageXml, $this->_serializableRelayState);
        }
    }
}
