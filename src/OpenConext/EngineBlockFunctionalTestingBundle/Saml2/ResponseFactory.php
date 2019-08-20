<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Saml2;

use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\AuthnRequest as SAMLAuthnRequest;
use SAML2\EncryptedAssertion;
use SAML2\XML\saml\SubjectConfirmation;

class ResponseFactory
{
    public function createForEntityWithRequest(
        MockIdentityProvider $mockIdp,
        SAMLAuthnRequest $request
    ) {
        // Note that we expect the Mock IdP to always have a 'template' Response.
        $response = $mockIdp->getResponse();

        $this->setResponseReferencesToRequest($request, $response);

        $this->setResponseStatus($mockIdp, $response);

        $this->setResponseSignatureKey($mockIdp, $response);

        $this->setResponseIssuer($mockIdp, $response);

        $this->encryptAssertions($mockIdp, $response);

        if ($mockIdp->shouldNotSendAssertions()) {
            $response->setAssertions([]);
        }

        if ($mockIdp->shouldTurnBackTheTime()) {
            // Set the timestamp to Unix Epoch
            $response->getAssertions()[0]->setNotOnOrAfter(0);
        }

        if ($mockIdp->isFromTheFuture()) {
            // Set the timestamp to current time + i year
            $response->getAssertions()[0]->setNotBefore(strtotime(date('Y-m-d H:i:s', strtotime('+1 year'))));
        }

        return $response;
    }

    /**
     * @param SAMLAuthnRequest $request
     * @param $response
     */
    private function setResponseReferencesToRequest(SAMLAuthnRequest $request, Response $response)
    {
        $response->setInResponseTo($request->getId());
        $assertions = $response->getAssertions();
        /** @var SubjectConfirmation[] $subjectConfirmations */
        $subjectConfirmations = $assertions[0]->getSubjectConfirmation();

        foreach ($subjectConfirmations as $subjectConfirmation) {
            $subjectConfirmation->SubjectConfirmationData->InResponseTo = $request->getId();
        }

        $assertions[0]->setSubjectConfirmation($subjectConfirmations);
    }

    /**
     * @param MockIdentityProvider $mockIdp
     * @param Response $response
     */
    private function setResponseStatus(MockIdentityProvider $mockIdp, Response $response)
    {
        $responseStatus = $response->getStatus();

        $statusOverride = [];
        $mockIdpTopStatusCode = $mockIdp->getStatusCodeTop();
        $mockIdpSubStatusCode = $mockIdp->getStatusCodeSecond();
        $mockIdpStatusMessage = $mockIdp->getStatusMessage();

        $statusOverride['Code'] = $mockIdpTopStatusCode;

        if (!empty($mockIdpSubStatusCode)) {
            $statusOverride['SubCode'] = $mockIdpSubStatusCode;
        } else {
            $statusOverride['SubCode'] = $responseStatus['SubCode'];
        }

        if ($mockIdpStatusMessage !== null) {
            $statusOverride['Message'] = $mockIdpStatusMessage;
        } elseif ($responseStatus['Message'] !== null) {
            $statusOverride['Message'] = $responseStatus['Message'];
        }

        $response->setStatus($statusOverride);
    }

    /**
     * @param MockIdentityProvider $mockIdp
     * @param $response
     */
    private function setResponseSignatureKey(MockIdentityProvider $mockIdp, Response $response)
    {
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $key->loadKey($mockIdp->getPrivateKeyPem());

        if ($mockIdp->mustSignResponses()) {
            $response->setSignatureKey($key);
        }

        if ($mockIdp->mustSignAssertions()) {
            $assertions = $response->getAssertions();
            foreach ($assertions as $assertion) {
                $assertion->setSignatureKey($key);
            }
        }
    }

    private function setResponseIssuer(MockIdentityProvider $mockIdp, Response $response)
    {
        $response->setIssuer($mockIdp->entityId());
    }

    private function encryptAssertions(MockIdentityProvider $mockIdp, Response $response)
    {
        $encryptionKey = $mockIdp->getEncryptionKey();
        if (!$encryptionKey) {
            return;
        }

        $encryptedAssertions = [];
        $assertions = $response->getAssertions();
        foreach ($assertions as $assertion) {
            $encryptedAssertion = new EncryptedAssertion();
            $encryptedAssertion->setAssertion($assertion, $encryptionKey);
            $encryptedAssertions[] = $encryptedAssertion;
        }
        $response->setAssertions($encryptedAssertions);
    }
}
