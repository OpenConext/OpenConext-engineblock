<?php

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlockBridge\ResponseFactory;
use Symfony\Component\HttpFoundation\Request;

class MetadataController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
    }

    /**
     * @param null|string $keyId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function idpMetadataAction($keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->idPMetadata();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @param null|string $keyId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function spMetadataAction($keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->sPMetadata();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @param null|string $keyId
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function allIdpsMetadataAction(Request $request, $keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->idPsMetadata();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @param null|string $keyId
     * @param Request     $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edugainMetadataAction(Request $request, $keyId = null)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        if ($keyId !== null) {
            $proxyServer->setKeyId($keyId);
        }

        $proxyServer->edugainMetadata();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
