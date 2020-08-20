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
use SAML2\XML\md\SPSSODescriptor;

/**
 * Class MockServiceProvider
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Mock
 * @SuppressWarnings("PMD")
 */
class MockServiceProvider extends AbstractMockEntityRole
{
    public function loginUrl()
    {
        return $this->loginUrlRedirect();
    }

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

    public function useUnsolicited()
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['Unsolicited' => true]
            )
        );
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

    public function trustedProxy()
    {
        $this->descriptor->setExtensions(
            array_merge(
                $this->descriptor->getExtensions(),
                ['TrustedProxy' => true]
            )
        );
        return $this;
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

    public function getAuthnContextClassRef(): string
    {
        return $this->descriptor->getExtensions()['AuthnContextClassRef'] ?? '';
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
}
