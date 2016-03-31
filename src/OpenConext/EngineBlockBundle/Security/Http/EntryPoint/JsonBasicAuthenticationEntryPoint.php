<?php

namespace OpenConext\EngineBlockBundle\Security\Http\EntryPoint;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * JsonBasicAuthenticationEntryPoint starts an HTTP Basic authentication with a JSON response body.
 */
class JsonBasicAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private $realmName;

    public function __construct($realmName)
    {
        $this->realmName = $realmName;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $authExceptionMessage = $authException ? $authException->getMessage() : '';
        $error                = sprintf(
            'You are required to authorise before accessing this API (%s).',
            $authExceptionMessage
        );
        $response             = new JsonResponse(
            ['errors' => [$error]],
            401,
            ['WWW-Authenticate' => sprintf('Basic realm="%s"', $this->realmName)]
        );

        return $response;
    }
}
