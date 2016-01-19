<?php

namespace OpenConext\EngineBlock\FunctionalTestingBundle\Mock;

use OpenConext\EngineBlock\FunctionalTestingBundle\Saml2\AuthnRequest;

/**
 * Class MockServiceProvider
 * @package OpenConext\EngineBlock\FunctionalTestingBundle\Mock
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
        return $this->descriptor->Extensions['LoginRedirectUrl'];
    }

    public function loginUrlPost()
    {
        return $this->descriptor->Extensions['LoginPostUrl'];
    }

    public function assertionConsumerServiceLocation()
    {
        /** @var \SAML2_XML_md_SPSSODescriptor $role */
        $role = $this->getSsoRole();
        return $role->AssertionConsumerService[0]->Location;
    }

    /**
     * @return AuthnRequest
     */
    public function getAuthnRequest()
    {
        return $this->descriptor->Extensions['SAMLRequest'];
    }

    public function setAuthnRequest(\SAML2_AuthnRequest $authnRequest)
    {
        $this->descriptor->Extensions['SAMLRequest'] = $authnRequest;
        return $this;
    }

    public function useIdpTransparently($entityId)
    {
        $this->descriptor->Extensions['TransparentIdp'] = $entityId;
        return $this;
    }

    public function getTransparentIdp()
    {
        return isset($this->descriptor->Extensions['TransparentIdp']) ?
            $this->descriptor->Extensions['TransparentIdp'] :
            '';
    }

    public function useUnsolicited()
    {
        $this->descriptor->Extensions['Unsolicited'] = true;
        return $this;
    }

    public function mustUseUnsolicited()
    {
        return isset($this->descriptor->Extensions['Unsolicited']) && $this->descriptor->Extensions['Unsolicited'];
    }

    public function signAuthnRequests()
    {
        /** @var \SAML2_XML_md_SPSSODescriptor $role */
        $role = $this->getSsoRole();
        $role->AuthnRequestsSigned = true;
        return $this;
    }

    public function mustSignAuthnRequests()
    {
        /** @var \SAML2_XML_md_SPSSODescriptor $role */
        $role = $this->getSsoRole();
        return $role->AuthnRequestsSigned;
    }

    public function useHttpPost()
    {
        $this->descriptor->Extensions['UsePost'] = true;
        return $this;
    }

    public function mustUsePost()
    {
        return isset($this->descriptor->Extensions['UsePost']);
    }

    public function useHttpRedirect()
    {
        unset($this->descriptor->Extensions['UsePost']);
        return $this;
    }

    public function isTrustedProxy()
    {
        return isset($this->descriptor->Extensions['TrustedProxy']) && $this->descriptor->Extensions['TrustedProxy'];
    }

    public function trustedProxy()
    {
        $this->descriptor->Extensions['TrustedProxy'] = true;
        return $this;
    }

    protected function getRoleClass()
    {
        return '\SAML2_XML_md_SPSSODescriptor';
    }
}
