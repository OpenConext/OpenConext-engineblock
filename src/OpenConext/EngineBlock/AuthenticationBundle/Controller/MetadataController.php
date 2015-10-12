<?php

namespace OpenConext\EngineBlock\AuthenticationBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException;
use EngineBlock_View;
use Janus_Client_CacheProxy_Exception;
use OpenConext\EngineBlock\CompatibilityBundle\Bridge\ResponseFactory;
use Symfony\Component\HttpFoundation\Request;

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

    public function spMetadataAction($virtualOrganization = null, $keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($virtualOrganization) {
            $proxyServer->setVirtualOrganisationContext($virtualOrganization);
        }

        if ($keyId) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->sPMetadata();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    public function allIdpsMetadataAction($virtualOrganization = null, $keyId = null, Request $request)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($virtualOrganization !== null) {
            $proxyServer->setVirtualOrganisationContext($virtualOrganization);
        }

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        try {
            $proxyServer->idPsMetadata();
        } catch (Janus_Client_CacheProxy_Exception $exception) {
            throw new EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException(
                $request->query->get('sp-entity-id'),
                $exception
            );
        }

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    public function edugainMetadataAction($keyId = null, Request $request)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        try {
            $proxyServer->edugainMetadata($request->getQueryString());
        } catch (Janus_Client_CacheProxy_Exception $exception) {
            throw new EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException(
                $request->query->get('sp-entity-id'),
                $exception
            );
        }
    }
}
