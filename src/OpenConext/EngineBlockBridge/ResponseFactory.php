<?php

namespace OpenConext\EngineBlockBridge;

use EngineBlock_Http_Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseFactory
{
    /**
     * @param EngineBlock_Http_Response $response
     * @return Response
     */
    public static function fromEngineBlockResponse(EngineBlock_Http_Response $response)
    {
        if ($response->getRedirectUrl()) {
            return new RedirectResponse($response->getRedirectUrl());
        }

        return new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders());
    }
}
