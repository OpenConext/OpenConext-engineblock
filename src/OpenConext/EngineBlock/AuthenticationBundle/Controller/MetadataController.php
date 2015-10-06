<?php

namespace OpenConext\EngineBlock\AuthenticationBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use EngineBlock_View;
use OpenConext\EngineBlock\CompatibilityBundle\Bridge\ResponseFactory;

class MetadataController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView                 = $engineBlockView;
    }

    public function idpMetadataAction($virtualOrganization = null, $keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($virtualOrganization) {
            $proxyServer->setVirtualOrganisationContext($virtualOrganization);
        }

        if ($keyId) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->idPMetadata();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
