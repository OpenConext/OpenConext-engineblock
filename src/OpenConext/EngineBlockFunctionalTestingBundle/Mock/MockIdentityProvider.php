<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use DOMElement;
use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Response;
use XMLSecurityKey;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) Allows for better control
 */
class MockIdentityProvider extends AbstractMockEntityRole
{
    public function singleSignOnLocation()
    {
        return $this->getSsoRole()->SingleSignOnService[0]->Location;
    }

    public function setResponse(Response $response)
    {
        /** @var \SAML2_XML_md_IDPSSODescriptor $role */
        $role = $this->getSsoRole();
        $role->Extensions['SAMLResponse'] = $response;

        return $this;
    }

    public function overrideResponseDestination($acsUrl)
    {
        $this->descriptor->Extensions['DestinationOverride'] = $acsUrl;
    }

    public function hasDestinationOverride()
    {
        return isset($this->descriptor->Extensions['DestinationOverride']);
    }

    public function getDestinationOverride()
    {
        return $this->descriptor->Extensions['DestinationOverride'];
    }

    public function setStatusMessage($statusMessage)
    {
        $role = $this->getSsoRole();

        $role->Extensions['StatusMessage'] = $statusMessage;
    }

    public function setStatusCode($topLevelStatusCode, $secondLevelStatusCode = '')
    {
        $role = $this->getSsoRole();

        $role->Extensions['StatusCodeTop'] = $this->getFullyQualifiedStatusCode($topLevelStatusCode);

        if (!empty($secondLevelStatusCode)) {
            $role->Extensions['StatusCodeSecond'] = $this->getFullyQualifiedStatusCode($secondLevelStatusCode);
        }
    }

    private function getFullyQualifiedStatusCode($shortStatusCode)
    {
        $class = new \ReflectionClass('\\SAML2_Const');
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

        throw new \RuntimeException("'$shortStatusCode' is not a valid status code");
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        /** @var \SAML2_XML_md_IDPSSODescriptor $role */
        $role = $this->getSsoRole();
        return $role->Extensions['SAMLResponse'];
    }

    public function getStatusCodeTop()
    {
        $role = $this->getSsoRole();

        if (!isset($role->Extensions['StatusCodeTop'])) {
            return \SAML2_Const::STATUS_SUCCESS;
        }

        return $role->Extensions['StatusCodeTop'];
    }

    public function getStatusCodeSecond()
    {
        $role = $this->getSsoRole();

        if (!isset($role->Extensions['StatusCodeSecond'])) {
            return '';
        }

        return $role->Extensions['StatusCodeSecond'];
    }

    public function getStatusMessage()
    {
        $role = $this->getSsoRole();

        if (!isset($role->Extensions['StatusMessage'])) {
            return '';
        }

        return $role->Extensions['StatusMessage'];
    }

    public function useHttpRedirect()
    {
        $this->descriptor->Extensions['UseRedirect'] = true;
        return $this;
    }

    public function useEncryptionCert($certFilePath)
    {
        $this->descriptor->Extensions['EncryptionCert'] = $certFilePath;
        // an encrypted response must be signed
        $this->useResponseSigning();

        return $this;
    }

    public function useEncryptionSharedKey($sharedKey)
    {
        $this->descriptor->Extensions['EncryptionSharedKey'] = $sharedKey;
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
        if (!isset($this->descriptor->Extensions['EncryptionCert'])) {
            return null;
        }

        $key = new XMLSecurityKey(XMLSecurityKey::RSA_1_5, array('type' => 'public'));
        $key->loadKey($this->findFile($this->descriptor->Extensions['EncryptionCert']), true, true);

        return $key;
    }

    protected function getSharedEncryptionKey()
    {
        if (!isset($this->descriptor->Extensions['EncryptionSharedKey'])) {
            return null;
        }

        $key = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
        $key->loadKey($this->descriptor->Extensions['EncryptionSharedKey']);

        return $key;
    }

    public function mustUseHttpRedirect()
    {
        return isset($this->descriptor->Extensions['UseRedirect']) && $this->descriptor->Extensions['UseRedirect'];
    }

    public function removeAttribute($forbiddenAttributeName)
    {
        $role = $this->getSsoRole();

        /** @var Response $response */
        $response = $role->Extensions['SAMLResponse'];
        $assertions = $response->getAssertions();

        $newAttributes = array();

        $attributes = $assertions[0]->getAttributes();
        foreach ($attributes as $attributeName => $attributeValues) {
            if ($attributeName === $forbiddenAttributeName) {
                continue;
            }

            $newAttributes[$attributeName] = $attributeValues;
        }

        $assertions[0]->setAttributes($newAttributes);
    }

    public function useResponseSigning()
    {
        $this->descriptor->Extensions['SignResponses'] = true;
        return $this;
    }

    public function mustSignResponses()
    {
        return isset($this->descriptor->Extensions['SignResponses']);
    }

    public function doNotUseAssertionSigning()
    {
        unset($this->descriptor->Extensions['SignAssertions']);
        return $this;
    }

    public function signAssertions()
    {
        return $this->descriptor->Extensions['SignAssertions'] = true;
    }

    public function mustSignAssertions()
    {
        return isset($this->descriptor->Extensions['SignAssertions']);
    }

    protected function getRoleClass()
    {
        return '\SAML2_XML_md_IDPSSODescriptor';
    }
}
