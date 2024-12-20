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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Response;
use ReflectionClass;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RuntimeException;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Response as SAMLResponse;
use SAML2\XML\md\IDPSSODescriptor;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Allows for better control
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MockIdentityProvider extends AbstractMockEntityRole
{
    private $sendAssertions = true;

    private $turnBackTime = false;

    private $fromTheFuture = false;

    public function singleSignOnLocation()
    {
        return $this->getSsoRole()->getSingleSignOnService()[0]->getLocation();
    }

    public function setResponse(Response $response)
    {
        $role = $this->getSsoRole();
        $role->setExtensions(['SAMLResponse' => $response]);

        return $this;
    }

    public function setStatusMessage($statusMessage)
    {
        $role = $this->getSsoRole();
        $role->setExtensions(array_merge($role->getExtensions(), ['StatusMessage' => $statusMessage]));
    }

    public function setStatusCode($topLevelStatusCode, $secondLevelStatusCode = '')
    {
        $role = $this->getSsoRole();

        $role->setExtensions(
            array_merge(
                $role->getExtensions(),
                ['StatusCodeTop' => $this->getFullyQualifiedStatusCode($topLevelStatusCode)]
            )
        );
        if (!empty($secondLevelStatusCode)) {
            $role->setExtensions(
                array_merge(
                    $role->getExtensions(),
                    ['StatusCodeSecond' => $this->getFullyQualifiedStatusCode($secondLevelStatusCode)]
                )
            );
        }
    }

    private function getFullyQualifiedStatusCode($shortStatusCode)
    {
        $class = new ReflectionClass(Constants::class);
        $constants = $class->getConstants();
        foreach ($constants as $constName => $constValue) {
            if (strpos($constName, 'STATUS_') !== 0) {
                continue;
            }

            if (strpos($constValue, $shortStatusCode) === false) {
                continue;
            }

            return $constValue;
        }

        throw new RuntimeException(sprintf('"%s" is not a valid status code', $shortStatusCode));
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        $role = $this->getSsoRole();
        return $role->getExtensions()['SAMLResponse'];
    }

    public function getStatusCodeTop()
    {
        $role = $this->getSsoRole();

        if (!isset($role->getExtensions()['StatusCodeTop'])) {
            return Constants::STATUS_SUCCESS;
        }

        return $role->getExtensions()['StatusCodeTop'];
    }

    public function getStatusCodeSecond()
    {
        $role = $this->getSsoRole();

        if (!isset($role->getExtensions()['StatusCodeSecond'])) {
            return '';
        }

        return $role->getExtensions()['StatusCodeSecond'];
    }

    public function getStatusMessage()
    {
        $role = $this->getSsoRole();

        if (!isset($role->getExtensions()['StatusMessage'])) {
            return '';
        }

        return $role->getExtensions()['StatusMessage'];
    }

    public function useHttpRedirect()
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['UseRedirect' => true]
            )
        );
        return $this;
    }

    public function useEncryptionCert($certFilePath)
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['EncryptionCert' => $certFilePath]
            )
        );
        // an encrypted response must be signed
        $this->useResponseSigning();

        return $this;
    }

    public function useEncryptionSharedKey($sharedKey)
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['EncryptionSharedKey' => $sharedKey]
            )
        );
        return $this;
    }

    /**
     * @return XMLSecurityKey
     */
    public function getEncryptionKey()
    {
        $encryptionKey = $this->getRsaEncryptionKey();
        if ($encryptionKey) {
            return $encryptionKey;
        }

        $encryptionKey = $this->getSharedEncryptionKey();
        if ($encryptionKey) {
            return $encryptionKey;
        }

        return null;
    }

    protected function getRsaEncryptionKey()
    {
        if (!isset($this->descriptor->getExtensions()['EncryptionCert'])) {
            return null;
        }

        $key = new XMLSecurityKey(XMLSecurityKey::RSA_OAEP_MGF1P, ['type' => 'public']);
        $key->loadKey($this->findFile($this->descriptor->getExtensions()['EncryptionCert']), true, true);

        return $key;
    }

    protected function getSharedEncryptionKey()
    {
        if (!isset($this->descriptor->getExtensions()['EncryptionSharedKey'])) {
            return null;
        }

        $key = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
        $key->loadKey($this->descriptor->getExtensions()['EncryptionSharedKey']);

        return $key;
    }

    public function mustUseHttpRedirect()
    {
        $extensions = $this->descriptor->getExtensions();
        return isset($extensions['UseRedirect']) && $extensions['UseRedirect'];
    }

    public function removeAttribute($forbiddenAttributeName)
    {
        $role = $this->getSsoRole();

        /** @var Response $response */
        $response = $role->getExtensions()['SAMLResponse'];
        $assertions = $response->getAssertions();

        $newAttributes = [];

        $attributes = $assertions[0]->getAttributes();
        foreach ($attributes as $attributeName => $attributeValues) {
            if ($attributeName === $forbiddenAttributeName) {
                continue;
            }

            $newAttributes[$attributeName] = $attributeValues;
        }

        $assertions[0]->setAttributes($newAttributes);
    }

    public function setAttribute($attributeName, array $attributeValues)
    {
        $role = $this->getSsoRole();

        /** @var Response $response */
        $response = $role->getExtensions()['SAMLResponse'];
        $assertions = $response->getAssertions();

        $attributes = $assertions[0]->getAttributes();
        $newAttributes = $attributes;

        $newAttributes[$attributeName] = $attributeValues;

        $assertions[0]->setAttributes($newAttributes);
    }

    public function setAuthnContextClassRef($authnContextClassRefValue)
    {
        $role = $this->getSsoRole();

        /** @var Response $response */
        $response = $role->getExtensions()['SAMLResponse'];
        $assertions = $response->getAssertions();

        $assertions[0]->setAuthnContextClassRef($authnContextClassRefValue);
    }


    public function useResponseSigning()
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['SignResponses' => true]
            )
        );
        return $this;
    }

    public function mustSignResponses()
    {
        return isset($this->descriptor->getExtensions()['SignResponses']);
    }

    public function doNotUseResponseSigning()
    {
        unset($this->descriptor->getExtensions()['SignResponses']);
        return $this;
    }

    public function signAssertions()
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['SignAssertions' => true]
            )
        );
    }

    public function mustSignAssertions()
    {
        return isset($this->descriptor->getExtensions()['SignAssertions']);
    }

    public function doNotSendAssertions()
    {
        $this->sendAssertions = false;

        return $this;
    }

    public function shouldTurnBackTheTime()
    {
        return $this->turnBackTime;
    }

    public function turnBackTheTime()
    {
        $this->turnBackTime = true;

        return $this;
    }

    public function isFromTheFuture()
    {
        return $this->fromTheFuture;
    }

    public function fromTheFuture()
    {
        $this->fromTheFuture = true;

        return $this;
    }

    public function shouldNotSendAssertions()
    {
        return $this->sendAssertions === false;
    }

    protected function getRoleClass()
    {
        return IDPSSODescriptor::class;
    }

    /**
     * Handle serialization of the MockIdentityProvider.
     * Convert the SAMLResponse (which contains non-serializable DOMDocument) to XML string.
     *
     * @return array
     */
    public function __sleep()
    {
        $role = $this->getSsoRole();
        $extensions = $role->getExtensions();

        // Convert SAMLResponse to XML if it exists
        if (isset($extensions['SAMLResponse']) && $extensions['SAMLResponse'] instanceof SAMLResponse) {
            $samlResponse = $extensions['SAMLResponse'];
            $xml = $samlResponse->toUnsignedXML()->ownerDocument->saveXML();

            // Store the XML and RelayState temporarily in the extensions
            $extensions['_SAMLResponseXML'] = $xml;
            $extensions['_SAMLResponseRelayState'] = $samlResponse->getRelayState();
            unset($extensions['SAMLResponse']);
            $role->setExtensions($extensions);
        }

        return ['name', 'descriptor', 'sendAssertions', 'turnBackTime', 'fromTheFuture'];
    }

    /**
     * Handle deserialization of the MockIdentityProvider.
     * Reconstruct the SAMLResponse from the stored XML string.
     */
    public function __wakeup()
    {
        $role = $this->getSsoRole();
        $extensions = $role->getExtensions();

        // Reconstruct SAMLResponse from XML if it was serialized
        if (isset($extensions['_SAMLResponseXML'])) {
            $xml = $extensions['_SAMLResponseXML'];

            // Parse the XML to get the DOMElement
            $document = DOMDocumentFactory::fromString($xml);
            $messageDomElement = $document->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:protocol', 'Response')->item(0);

            if ($messageDomElement) {
                // Create a custom Response instance by passing the DOMElement to the constructor
                // This properly initializes all the parent class properties
                $samlResponse = new Response($messageDomElement);

                // Restore RelayState if it was stored
                if (isset($extensions['_SAMLResponseRelayState']) && $extensions['_SAMLResponseRelayState'] !== null) {
                    $samlResponse->setRelayState($extensions['_SAMLResponseRelayState']);
                }

                // DO NOT set the XML string - let the Response object generate signed XML dynamically
                // when toXml() is called with the signature keys that will be set by ResponseFactory

                // Restore it to the extensions
                unset($extensions['_SAMLResponseXML'], $extensions['_SAMLResponseRelayState']);
                $extensions['SAMLResponse'] = $samlResponse;
                $role->setExtensions($extensions);
            }
        }
    }
}
