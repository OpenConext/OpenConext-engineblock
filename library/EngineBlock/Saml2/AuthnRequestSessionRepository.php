<?php

/**
 * Session storage for Authentication Requests. Store AuthnRequests and link requests together.
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

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $spRequest
     * @return $this
     */
    public function store(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $spRequest
    ) {
        // Store the original Request
        $this->requestStorage[$spRequest->getId()] = $spRequest;
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
        // Store the mapping from the new request ID to the original request ID
        $this->linkStorage[$fromRequest->getId()] = $toRequest->getId();
        return $this;
    }
}