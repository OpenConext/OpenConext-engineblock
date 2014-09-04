<?php

class EngineBlock_Group_Provider_Precondition_OpenSocial_Oauth_AccessTokenExists implements EngineBlock_Group_Provider_Precondition_Interface
{
    /**
     * @var \EngineBlock_Group_Provider_OpenSocial_Oauth_ThreeLegged
     */
    protected $_provider;

    public function __construct(EngineBlock_Group_Provider_Interface $provider, Zend_Config $options = null)
    {
        $this->_provider = $provider;
    }

    public function validate()
    {
        return (bool)$this->_provider->getOpenSocialRestClient()->getHttpClient()->getToken()->getToken();
    }
}