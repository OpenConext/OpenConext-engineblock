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

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Session storage for Authentication Requests. Store AuthnRequests and link requests together.
 */
class EngineBlock_Saml2_AuthnRequestSessionRepository
{
    private const SESSION_KEY_REQUESTS = 'SAMLRequest';
    private const SESSION_KEY_LINKS    = 'SAMLRequestLinks';

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param string $requestId
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator|null
     */
    public function findRequestById($requestId)
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return null;
        }

        return $session->get(self::SESSION_KEY_REQUESTS, [])[$requestId] ?? null;
    }

    /**
     * @param string|null $requestId
     * @return string|null
     */
    public function findLinkedRequestId($requestId)
    {
        if (!$requestId) {
            return null;
        }

        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return null;
        }

        return $session->get(self::SESSION_KEY_LINKS, [])[$requestId] ?? null;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $spRequest
     * @return $this
     */
    public function store(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $spRequest)
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return $this;
        }

        $requests = $session->get(self::SESSION_KEY_REQUESTS, []);
        $requests[$spRequest->getId()] = $spRequest;
        $session->set(self::SESSION_KEY_REQUESTS, $requests);

        return $this;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $fromRequest
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $toRequest
     * @return $this
     */
    public function link(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $fromRequest,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $toRequest
    ) {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return $this;
        }

        $links = $session->get(self::SESSION_KEY_LINKS, []);
        $links[$fromRequest->getId()] = $toRequest->getId();
        $session->set(self::SESSION_KEY_LINKS, $links);

        return $this;
    }

}
