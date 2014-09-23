<?php

abstract class EngineBlock_Corto_Filter_Command_Abstract implements EngineBlock_Corto_Filter_Command_Interface
{
    /**
     * @var bool
     */
    protected $_continueFiltering = TRUE;

    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    protected $_server;

    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    protected $_response;

    /**
     * @var array
     */
    protected $_responseAttributes;

    /**
     * @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_spMetadata;

    /**
     * @var array
     */
    protected $_idpMetadata;

    /**
     * @var string
     */
    protected $_collabPersonId;

    /**
     * @return bool
     */
    public function mustContinueFiltering()
    {
        return $this->_continueFiltering;
    }

    /**
     * @return \EngineBlock_Corto_Filter_Command_Abstract
     * @return $this
     */
    public function stopFiltering()
    {
        $this->_continueFiltering = FALSE;
        return $this;
    }

    /**
     * @param \EngineBlock_Corto_ProxyServer $server
     * @return $this
     */
    public function setProxyServer(EngineBlock_Corto_ProxyServer $server)
    {
        $this->_server = $server;
        return $this;
    }

    /**
     * @param array $idpMetadata
     * @return $this
     */
    public function setIdpMetadata(array $idpMetadata)
    {
        $this->_idpMetadata = $idpMetadata;
        return $this;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @return $this
     */
    public function setRequest(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @return $this
     */
    public function setResponse(EngineBlock_Saml2_ResponseAnnotationDecorator $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * @param array $responseAttributes
     * @return $this
     */
    public function setResponseAttributes(array $responseAttributes)
    {
        $this->_responseAttributes = $responseAttributes;
        return $this;
    }

    /**
     * @param array $spMetadata
     * @return $this
     */
    public function setSpMetadata(array $spMetadata)
    {
        $this->_spMetadata = $spMetadata;
        return $this;
    }

    /**
     * @param $collabPersonId
     * @return $this
     */
    public function setCollabPersonId($collabPersonId)
    {
        $this->_collabPersonId = $collabPersonId;
        return $this;
    }

    /**
     * Check the existence of collabPersonId
     */
    public function invariant()
    {
        if (!$this->_collabPersonId) {
            throw new EngineBlock_Corto_Filter_Command_Exception_PreconditionFailed(
                'Missing collabPersonId'
            );
        }

    }
}
