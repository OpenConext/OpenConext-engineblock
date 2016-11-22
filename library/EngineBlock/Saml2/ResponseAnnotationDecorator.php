<?php

/**
 * Annotate the response with our own metadata
 */
class EngineBlock_Saml2_ResponseAnnotationDecorator extends EngineBlock_Saml2_MessageAnnotationDecorator
{
    /**
     * @var SAML2_Response
     */
    protected $sspMessage;

    /**
     * @var string
     */
    protected $return;

    /**
     * @var string
     */
    protected $originalIssuer;

    /**
     * @var string
     */
    protected $originalNameId;

    /**
     * @var string
     */
    protected $originalBinding;

    /**
     * @var string
     */
    protected $originalResponse;

    /**
     * @var string
     */
    protected $collabPersonId;

    /**
     * @var string
     */
    protected $customNameId;

    /**
     * @var string
     */
    protected $intendedNameId;

    /**
     * @param SAML2_Response $response
     */
    function __construct(SAML2_Response $response)
    {
        $this->sspMessage = $response;
    }

    /**
     * @return SAML2_Assertion
     * @throws RuntimeException
     */
    public function getAssertion()
    {
        $assertions = $this->sspMessage->getAssertions();
        if (empty($assertions)) {
            throw new \RuntimeException('No assertions in response?');
        }
        return $assertions[0];
    }

    public function getNameId()
    {
        $assertion = $this->getAssertion();
        return $assertion->getNameId();
    }

    public function getNameIdValue()
    {
        $nameId = $this->getNameId();
        if (empty($nameId['Value'])) {
            throw new \RuntimeException('No NameID in Assertion?');
        }
        return $nameId['Value'];
    }

    public function getNameIdFormat()
    {
        $nameId = $this->getNameId();
        if (empty($nameId['Format'])) {
            throw new \RuntimeException('No NameID in Assertion?');
        }
        return $nameId['Format'];
    }

    /**
     * @return array
     */
    public function getStatus()
    {
        return $this->sspMessage->getStatus();
    }

    /**
     * @return SAML2_Assertion[]|SAML2_EncryptedAssertion[]
     */
    public function getAssertions()
    {
        return $this->sspMessage->getAssertions();
    }

    /**
     * @return bool
     */
    public function hasAssertion()
    {
        $assertions = $this->sspMessage->getAssertions();

        return !empty($assertions);
    }

    /**
     * @param string $return
     * @return $this
     */
    public function setReturn($return)
    {
        $this->return = $return;
        return $this;
    }

    /**
     * @return string
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @param string $collabPersonId
     * @return $this
     */
    public function setCollabPersonId($collabPersonId)
    {
        $this->collabPersonId = $collabPersonId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollabPersonId()
    {
        return $this->collabPersonId;
    }

    /**
     * @param string $originalNameId
     * @return $this
     */
    public function setOriginalNameId($originalNameId)
    {
        $this->originalNameId = $originalNameId;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalNameId()
    {
        return $this->originalNameId;
    }

    /**
     * @param array $customNameId
     * @return $this
     */
    public function setCustomNameId(array $customNameId)
    {
        $this->customNameId = $customNameId;
        return $this;
    }

    /**
     * @return array
     */
    public function getCustomNameId()
    {
        return $this->customNameId;
    }

    /**
     * @param string $intendedNameId
     * @return $this
     */
    public function setIntendedNameId($intendedNameId)
    {
        $this->intendedNameId = $intendedNameId;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntendedNameId()
    {
        return $this->intendedNameId;
    }

    /**
     * @param string $originalBinding
     * @return $this
     */
    public function setOriginalBinding($originalBinding)
    {
        $this->originalBinding = $originalBinding;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalBinding()
    {
        return $this->originalBinding;
    }

    /**
     * @param string $originalIssuer
     * @return $this
     */
    public function setOriginalIssuer($originalIssuer)
    {
        $this->originalIssuer = $originalIssuer;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalIssuer()
    {
        return $this->originalIssuer;
    }

    /**
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $originalResponse
     * @return $this
     */
    public function setOriginalResponse(EngineBlock_Saml2_ResponseAnnotationDecorator $originalResponse)
    {
        $this->originalResponse = $originalResponse;
        return $this;
    }

    /**
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    public function getOriginalResponse()
    {
        return $this->originalResponse;
    }
}
