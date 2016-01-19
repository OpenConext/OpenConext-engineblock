<?php

namespace OpenConext\EngineBlock\FunctionalTestingBundle\Saml2;

use OpenConext\EngineBlock\FunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlock\FunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlock\FunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlock\FunctionalTestingBundle\Service\EngineBlock;
use XMLSecurityKey;

class ResponseFactory
{
    public function createForEntityWithRequest(
        MockIdentityProvider $mockIdp,
        \SAML2_AuthnRequest $request
    ) {
        // Note that we expect the Mock IdP to always have a 'template' Response.
        $response = $mockIdp->getResponse();

        $this->setResponseReferencesToRequest($request, $response);

        $this->setResponseStatus($mockIdp, $response);

        $this->setResponseSignatureKey($mockIdp, $response);

        $this->setResponseIssuer($mockIdp, $response);

        $this->encryptAssertions($mockIdp, $response);

        return $response;
    }

    /**
     * @param \SAML2_AuthnRequest $request
     * @param $response
     */
    private function setResponseReferencesToRequest(\SAML2_AuthnRequest $request, Response $response)
    {
        $response->setInResponseTo($request->getId());
        $assertions = $response->getAssertions();
        /** @var \SAML2_XML_saml_SubjectConfirmation[] $subjectConfirmations */
        $subjectConfirmations = $assertions[0]->getSubjectConfirmation();

        foreach ($subjectConfirmations as $subjectConfirmation) {
            $subjectConfirmation->SubjectConfirmationData->InResponseTo = $request->getId();
        }

        $assertions[0]->setSubjectConfirmation($subjectConfirmations);
    }

    /**
     * @param MockIdentityProvider $mockIdp
     * @param $response
     */
    private function setResponseStatus(MockIdentityProvider $mockIdp, Response $response)
    {
        $status = $response->getStatus();
        $status['Code'] = $mockIdp->getStatusCodeTop();

        $secondStatusCode = $mockIdp->getStatusCodeSecond();
        if (!empty($secondStatusCode)) {
            $status['SubCode'] = $secondStatusCode;
        }

        $status['Message'] = $mockIdp->getStatusMessage();
        $response->setStatus($status);
    }

    /**
     * @param MockIdentityProvider $mockIdp
     * @param $response
     */
    private function setResponseSignatureKey(MockIdentityProvider $mockIdp, Response $response)
    {
        $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
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

        $encryptedAssertions = array();
        $assertions = $response->getAssertions();
        foreach ($assertions as $assertion) {
            $encryptedAssertion = new \SAML2_EncryptedAssertion();
            $encryptedAssertion->setAssertion($assertion, $encryptionKey);
            $encryptedAssertions[] = $encryptedAssertion;
        }
        $response->setAssertions($encryptedAssertions);
    }
}
