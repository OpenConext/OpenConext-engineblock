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

/**
 * Session storage for Authentication Requests. Store AuthnRequests and link requests together.
 *
 * Uses three session keys:
 *   'SAMLRequest'               — map of requestId => AuthnRequestAnnotationDecorator
 *   'SAMLRequestLinks'          — map of idpRequestId => spRequestId
 *   'SAMLRequestLinkedRequests' — map of idpRequestId => AuthnRequestAnnotationDecorator
 *
 * 'SAMLRequest' is keyed on the ID chosen by the SP and can therefore be overwritten by a later request.
 * Lookups after the IdP has responded must use findLinkedRequest(), which is keyed on the ID generated
 * by EngineBlock and returns the exact request that was sent to the IdP.
 */
class EngineBlock_Saml2_AuthnRequestSessionRepository
{
    /**
     * @var Psr\Log\LoggerInterface
     */
    private $sessionLog;

    /**
     * @var
     */
    private $requestStorage;

    /**
     * @var array
     */
    private $linkStorage;

    /**
     * @var array
     */
    private $linkedRequestStorage;

    /**
     * @param Psr\Log\LoggerInterface $sessionLog
     */
    public function __construct(Psr\Log\LoggerInterface $sessionLog)
    {
        if (!isset($_SESSION['SAMLRequest'])) {
            $_SESSION['SAMLRequest'] = array();
        }
        $this->requestStorage = &$_SESSION['SAMLRequest'];

        if (!isset($_SESSION['SAMLRequestLinks'])) {
            $_SESSION['SAMLRequestLinks'] = array();
        }
        $this->linkStorage    = &$_SESSION['SAMLRequestLinks'];

        if (!isset($_SESSION['SAMLRequestLinkedRequests'])) {
            $_SESSION['SAMLRequestLinkedRequests'] = array();
        }
        $this->linkedRequestStorage = &$_SESSION['SAMLRequestLinkedRequests'];

        $this->sessionLog = $sessionLog;
    }

    /**
     * @param string $requestId
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    public function findRequestById($requestId)
    {
        if (!isset($this->requestStorage[$requestId])) {
            return null;
        }

        return $this->requestStorage[$requestId];
    }

    /**
     * @param $requestId
     * @return string|null
     */
    public function findLinkedRequestId($requestId)
    {
        // Check the session for a AuthnRequest with the given ID
        // Expect to get back an AuthnRequest issued by EngineBlock and destined for the IdP
        if (!$requestId || !isset($this->linkStorage[$requestId])) {
            return null;
        }

        return $this->linkStorage[$requestId];
    }

    public function findLinkedRequest(?string $requestId): ?EngineBlock_Saml2_AuthnRequestAnnotationDecorator
    {
        if (!$requestId) {
            return null;
        }

        return $this->linkedRequestStorage[$requestId] ?? null;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $spRequest
     * @return $this
     */
    public function store(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $spRequest
    ) {
        $this->assertNoIssuerConflict($this->findRequestById($spRequest->getId()), $spRequest);

        // Store the original Request
        $this->requestStorage[$spRequest->getId()] = $spRequest;
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
        // Store the mapping from the new request ID to the original request ID
        $this->linkStorage[$fromRequest->getId()] = $toRequest->getId();
        $this->linkedRequestStorage[$fromRequest->getId()] = $toRequest;
        return $this;
    }
}
