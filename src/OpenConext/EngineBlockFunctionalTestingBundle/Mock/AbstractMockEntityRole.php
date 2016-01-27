<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

/**
 * Class AbstractMockEntityRole
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Mock
 */
abstract class AbstractMockEntityRole
{
    protected $name;
    protected $descriptor;

    public function __construct(
        $name,
        \SAML2_XML_md_EntityDescriptor $descriptor
    ) {
        $this->name = $name;
        $this->descriptor = $descriptor;
    }

    public function entityId()
    {
        return $this->descriptor->entityID;
    }

    public function getEntityDescriptor()
    {
        return $this->descriptor;
    }

    public function setEntityId($entityId)
    {
        $this->descriptor->entityID = $entityId;
        return $this;
    }

    public function publicKeyCertData()
    {
        $role = $this->getSsoRole();

        foreach ($role->KeyDescriptor[0]->KeyInfo->info as $info) {
            if (!$info instanceof \SAML2_XML_ds_X509Data) {
                continue;
            }

            foreach ($info->data as $data) {
                if (!$data instanceof \SAML2_XML_ds_X509Certificate) {
                    continue;
                }

                return $data->certificate;
            }
        }
        throw new \RuntimeException("MockIdp does not have KeyInfo with an X509Certificate");
    }

    public function setCertificate($certificateFile)
    {
        $certData = str_replace(
            array("-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----", "\n"),
            '',
            $this->readFile($certificateFile)
        );

        $role = $this->getSsoRole();

        foreach ($role->KeyDescriptor[0]->KeyInfo->info as $info) {
            if (!$info instanceof \SAML2_XML_ds_X509Data) {
                continue;
            }

            foreach ($info->data as $data) {
                if (!$data instanceof \SAML2_XML_ds_X509Certificate) {
                    continue;
                }

                $data->certificate = $certData;
                return;
            }
        }
        throw new \RuntimeException("MockIdp does not have KeyInfo with an X509Certificate");
    }

    public function setPrivateKey($privateKeyFile)
    {
        $role = $this->getSsoRole();

        foreach ($role->KeyDescriptor[0]->KeyInfo->info as $info) {
            if (!$info instanceof \SAML2_XML_Chunk) {
                continue;
            }

            if ($info->localName !== 'PrivateKey') {
                continue;
            }

            $info->xml->nodeValue = $this->readFile($privateKeyFile);
            return;
        }

        throw new \RuntimeException("Unable to set private key, no KeyInfo with PrivateKey element set");
    }

    public function getPrivateKeyPem()
    {
        /** @var \SAML2_XML_md_SPSSODescriptor $spssoRole */
        $idpSsoRole = $this->getSsoRole();

        /** @var \SAML2_XML_Chunk $certificate */
        $certificate = array_reduce(
            $idpSsoRole->KeyDescriptor[0]->KeyInfo->info,
            function ($carry, $info) {
                return $carry ? $carry : $info instanceof \SAML2_XML_Chunk ? $info : false;
            }
        );

        return $certificate->xml->textContent;
    }

    /**
     * @return \SAML2_XML_md_SSODescriptorType
     * @throws \RuntimeException
     */
    protected function getSsoRole()
    {
        $roleClass = $this->getRoleClass();
        foreach ($this->descriptor->RoleDescriptor as $role) {
            if (!$role instanceof $roleClass) {
                continue;
            }

            return $role;
        }
        throw new \RuntimeException('No IDPSSODescriptor for MockIdentityProvider?');
    }

    abstract protected function getRoleClass();

    protected function readFile($filePath)
    {
        return file_get_contents($this->findFile($filePath));
    }

    protected function findFile($filePath)
    {
        if (file_exists($filePath)) {
            return $filePath;
        }

        $componentPath = __DIR__ . '/../../../../';
        $fullFilePath = realpath($componentPath . $filePath);
        if (file_exists($fullFilePath)) {
            return $fullFilePath;
        }

        $pathFromRoot = ENGINEBLOCK_FOLDER_ROOT . $filePath;
        if (file_exists($pathFromRoot)) {
            return $pathFromRoot;
        }

        throw new \RuntimeException('Unable to find file: ' . $filePath . " ($fullFilePath)");
    }
}
