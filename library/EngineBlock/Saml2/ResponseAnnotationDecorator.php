<?php

use SAML2\Assertion;
use SAML2\EncryptedAssertion;
use SAML2\Response;
use SAML2\XML\saml\NameID;

/**
 * Annotate the response with our own metadata
 *
 * @method void setStatus(array)
 * @method void setAssertions(array)
 * @method void setIssuer(string)
 */
class EngineBlock_Saml2_ResponseAnnotationDecorator extends EngineBlock_Saml2_MessageAnnotationDecorator
{
    /**
     * @var Response
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
     * @var null|NameID
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
     * @var NameID
     */
    protected $customNameId;

    /**
     * @var string
     */
    protected $intendedNameId;

    /**
     * @param Response $response
     */
    function __construct(Response $response)
    {
        $this->sspMessage = $response;
    }

    /**
     * @return Assertion
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

    /**
     * @return NameID|null The name identifier of the assertion.
     */
    public function getNameId()
    {
        $assertion = $this->getAssertion();
        return $assertion->getNameId();
    }

    public function getNameIdValue()
    {
        $nameId = $this->getNameId();
        if (!$nameId) {
            throw new \RuntimeException('No NameID in Assertion?');
        }
        return $nameId->value;
    }

    public function getNameIdFormat()
    {
        $nameId = $this->getNameId();
        if (!$nameId->Format) {
            throw new \RuntimeException('No NameID in Assertion?');
        }
        return $nameId->Format;
    }

    /**
     * @return array
     */
    public function getStatus()
    {
        return $this->sspMessage->getStatus();
    }

    /**
     * @return Assertion[]|EncryptedAssertion[]
     */
    public function getAssertions()
    {
        return $this->sspMessage->getAssertions();
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
     * @param array|NameID|null $originalNameId
     * @return $this
     * @throws EngineBlock_Exception
     */
    public function setOriginalNameId($originalNameId)
    {
        // This is added to support old style manipulation code where NameID is represented as an array
        if (is_array($originalNameId)) {
            $originalNameId = NameID::fromArray($originalNameId);
        }

        if (!$originalNameId instanceof NameID && !is_null($originalNameId)) {
            throw new EngineBlock_Exception(
                'An unsupported type for OriginalNameId was provided. It can either be array, NameID or null'
            );
        }

        $this->originalNameId = $originalNameId;

        return $this;
    }

    /**
     * @return null|NameID
     */
    public function getOriginalNameId()
    {
        return $this->originalNameId;
    }

    /**
     * @param $customNameId
     * @return $this
     * @throws EngineBlock_Exception
     */
    public function setCustomNameId($customNameId)
    {
        // This is added to support old style manipulation code where NameID is represented as an array
        if (is_array($customNameId)) {
            $customNameId = NameID::fromArray($customNameId);
        }

        if (!$customNameId instanceof NameID && !is_null($customNameId)) {
            throw new EngineBlock_Exception(
                'An unsupported type for CustomNameId was provided. It can either be array, NameID or null'
            );
        }

        $this->customNameId = $customNameId;
        return $this;
    }

    /**
     * @return NameID
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
