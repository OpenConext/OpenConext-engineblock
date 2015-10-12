<?php

namespace OpenConext\EngineBlock\AuthenticationBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlock\CompatibilityBundle\Bridge\ResponseFactory;

class CertificateController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    public function __construct(EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton)
    {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
    }

    public function idpSigningCertificateAction($keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->idpCertificate();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    public function spSigningCertificateAction($virtualOrganization = null, $keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        if ($virtualOrganization !== null) {
            $proxyServer->setVirtualOrganisationContext($virtualOrganization);
        }

        $proxyServer->spCertificate();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
