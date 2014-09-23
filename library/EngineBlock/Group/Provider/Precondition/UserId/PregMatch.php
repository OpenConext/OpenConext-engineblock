<?php

class EngineBlock_Group_Provider_Precondition_UserId_PregMatch implements EngineBlock_Group_Provider_Precondition_Interface
{
    protected $_provider;
    protected $_search;

    public function __construct(EngineBlock_Group_Provider_Interface $provider, Zend_Config $options)
    {
        $this->_provider = $provider;
        $this->_search = $options->search;
    }

    public function validate()
    {
        return @preg_match($this->_search, $this->_provider->getUserId());
    }
}
