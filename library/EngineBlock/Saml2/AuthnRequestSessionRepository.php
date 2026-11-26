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
 *
 * Uses the Symfony session bag under three keys:
 *   'SAMLRequest'               — map of requestId => AuthnRequestAnnotationDecorator
 *   'SAMLRequestLinks'          — map of idpRequestId => spRequestId
 *   'SAMLRequestLinkedRequests' — map of idpRequestId => AuthnRequestAnnotationDecorator
 *
 * 'SAMLRequest' is keyed on the ID chosen by the SP and can therefore be overwritten by a later request.
 * Lookups after the IdP has responded must use findLinkedRequest(), which is keyed on the ID generated
 * by EngineBlock and returns the exact request that was sent to the IdP.
 *
 * All methods are safe no-ops when no session is available (unit tests, CLI).
 */
class EngineBlock_Saml2_AuthnRequestSessionRepository
{
    private const string SESSION_KEY_REQUESTS = 'SAMLRequest';
    private const string SESSION_KEY_LINKS    = 'SAMLRequestLinks';
    private const string SESSION_KEY_LINKED_REQUESTS = 'SAMLRequestLinkedRequests';

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

    public function findLinkedRequest(?string $requestId): ?EngineBlock_Saml2_AuthnRequestAnnotationDecorator
    {
        if (!$requestId) {
            return null;
        }

        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return null;
        }

        return $session->get(self::SESSION_KEY_LINKED_REQUESTS, [])[$requestId] ?? null;
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
        $this->assertNoIssuerConflict($requests[$spRequest->getId()] ?? null, $spRequest);
        $requests[$spRequest->getId()] = $spRequest;
        $session->set(self::SESSION_KEY_REQUESTS, $requests);

        return $this;
    }

    /**
     * A request ID in this session may never be reused by a different Service Provider, as the consent and
     * processing state are keyed on the request ID alone. A trusted proxy uses its last RequesterID to identify
     * the Service Provider behind it, so that RequesterID must match as well.
     *
     * @throws EngineBlock_Corto_Module_Bindings_Exception
     */
    public function assertRequestIdNotUsedByOtherIssuer(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request): void
    {
        $this->assertNoIssuerConflict($this->findRequestById($request->getId()), $request);
    }

    private function assertNoIssuerConflict(
        ?EngineBlock_Saml2_AuthnRequestAnnotationDecorator $existing,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $new
    ): void {
        if ($existing === null) {
            return;
        }

        if ($existing->getIssuer()?->getValue() !== $new->getIssuer()?->getValue()
            || $this->getLastRequesterId($existing) !== $this->getLastRequesterId($new)
        ) {
            throw new EngineBlock_Corto_Module_Bindings_Exception(
                'AuthnRequest ID is already in use in this session by another Service Provider'
            );
        }
    }

    private function getLastRequesterId(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request): ?string
    {
        $requesterIds = $request->getRequesterIds();

        return empty($requesterIds) ? null : end($requesterIds);
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

        $linkedRequests = $session->get(self::SESSION_KEY_LINKED_REQUESTS, []);
        $linkedRequests[$fromRequest->getId()] = $toRequest;
        $session->set(self::SESSION_KEY_LINKED_REQUESTS, $linkedRequests);

        return $this;
    }
}
