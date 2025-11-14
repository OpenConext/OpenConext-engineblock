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

use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\AuthnRequest;
use SAML2\AuthnRequest as SAMLAuthnRequest;
use SAML2\DOMDocumentFactory;
use SAML2\XML\md\SPSSODescriptor;

/**
 * Class MockServiceProvider
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Mock
 * @SuppressWarnings("PMD")
 */
class MockServiceProvider extends AbstractMockEntityRole
{
    public function loginUrlRedirect()
    {
        return $this->descriptor->getExtensions()['LoginRedirectUrl'];
    }

    public function loginUrlPost()
    {
        return $this->descriptor->getExtensions()['LoginPostUrl'];
    }

    public function assertionConsumerServiceLocation()
    {
        /** @var SPSSODescriptor $role */
        $role = $this->getSsoRole();
        return $role->getAssertionConsumerService()[0]->getLocation();
    }

    /**
     * @return AuthnRequest
     */
    public function getAuthnRequest()
    {
        return $this->descriptor->getExtensions()['SAMLRequest'];
    }

    public function setAuthnRequest(SAMLAuthnRequest $authnRequest)
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['SAMLRequest' => $authnRequest]
            )
        );
    }

    public function useIdpTransparently($entityId)
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['TransparentIdp' => $entityId]
            )
        );
    }

    public function getTransparentIdp()
    {
        $extenstions = $this->descriptor->getExtensions();
        return $extenstions['TransparentIdp'] ?? '';
    }

    public function mustUseUnsolicited()
    {
        $extension = $this->descriptor->getExtensions();
        return isset($extension['Unsolicited']) && $extension['Unsolicited'];
    }

    public function signAuthnRequests()
    {
        /** @var SPSSODescriptor $role */
        $role = $this->getSsoRole();
        $role->setAuthnRequestsSigned(true);
        return $this;
    }

    public function mustSignAuthnRequests()
    {
        /** @var SPSSODescriptor $role */
        $role = $this->getSsoRole();
        return $role->getAuthnRequestsSigned();
    }

    public function useHttpPost()
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['UsePost' => true]
            )
        );
    }

    public function mustUsePost()
    {
        return isset($this->descriptor->getExtensions()['UsePost']);
    }

    public function useHttpRedirect()
    {
        unset($this->descriptor->getExtensions()['UsePost']);
    }

    public function isTrustedProxy()
    {
        $descriptor = $this->descriptor;
        return isset($descriptor->getExtensions()['TrustedProxy']) && $descriptor->getExtensions()['TrustedProxy'];
    }

    public function setAuthnRequestProxyCountTo($proxyCount)
    {
        $this->descriptor->getExtensions()['SAMLRequest']->setProxyCount($proxyCount);
    }

    public function setAuthnRequestToPassive()
    {
        $this->descriptor->getExtensions()['SAMLRequest']->setIsPassive(true);
    }

    /**
     * @param string $idpEntityId
     */
    public function addIdpToScope($idpEntityId)
    {
        $scope = $this->getScoping();
        $scope[] = $idpEntityId;

        $this->descriptor->getExtensions()['SAMLRequest']->setIDPList($scope);
    }

    public function sendMalformedAuthNRequest()
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['Malformed' => true]
            )
        );
    }

    /**
     * @return array
     */
    public function getScoping()
    {
        return $this->descriptor->getExtensions()['SAMLRequest']->getIDPList();
    }

    protected function getRoleClass()
    {
        return SPSSODescriptor::class;
    }

    public function setAuthnContextClassRef($classRef)
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['AuthnContextClassRef' => $classRef]
            )
        );
    }

    /**
     * Handle serialization of the MockServiceProvider.
     * Convert the SAMLRequest (which contains non-serializable DOMDocument) to XML string.
     *
     * @return array
     */
    public function __sleep()
    {
        $extensions = $this->descriptor->getExtensions();

        // Convert SAMLRequest to XML if it exists
        if (isset($extensions['SAMLRequest']) && $extensions['SAMLRequest'] instanceof SAMLAuthnRequest) {
            $samlRequest = $extensions['SAMLRequest'];
            $xml = $samlRequest->toUnsignedXML()->ownerDocument->saveXML();

            // Store the XML and RelayState temporarily in the extensions
            $extensions['_SAMLRequestXML'] = $xml;
            $extensions['_SAMLRequestRelayState'] = $samlRequest->getRelayState();
            unset($extensions['SAMLRequest']);
            $this->descriptor->setExtensions($extensions);
        }

        return ['name', 'descriptor'];
    }

    /**
     * Handle deserialization of the MockServiceProvider.
     * Reconstruct the SAMLRequest from the stored XML string.
     */
    public function __wakeup()
    {
        $extensions = $this->descriptor->getExtensions();

        // Reconstruct SAMLRequest from XML if it was serialized
        if (isset($extensions['_SAMLRequestXML'])) {
            $xml = $extensions['_SAMLRequestXML'];

            // Parse the XML to get the DOMElement
            $document = DOMDocumentFactory::fromString($xml);
            $messageDomElement = $document->getElementsByTagNameNS('urn:oasis:names:tc:SAML:2.0:protocol', 'AuthnRequest')->item(0);

            if ($messageDomElement) {
                // Create a custom AuthnRequest instance by passing the DOMElement to the constructor
                // This properly initializes all the parent class properties
                $samlRequest = new AuthnRequest($messageDomElement);

                // Restore RelayState if it was stored
                if (isset($extensions['_SAMLRequestRelayState']) && $extensions['_SAMLRequestRelayState'] !== null) {
                    $samlRequest->setRelayState($extensions['_SAMLRequestRelayState']);
                }

                // DO NOT set the XML string - let the AuthnRequest object generate signed XML dynamically
                // when toXml() is called with the signature keys if signing is configured

                // Restore it to the extensions
                unset($extensions['_SAMLRequestXML'], $extensions['_SAMLRequestRelayState']);
                $extensions['SAMLRequest'] = $samlRequest;
                $this->descriptor->setExtensions($extensions);
            }
        }
    }
}
