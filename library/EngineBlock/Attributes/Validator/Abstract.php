<?php

abstract class EngineBlock_Attributes_Validator_Abstract implements EngineBlock_Attributes_Validator_Interface
{
    protected $_attributeName;
    protected $_attributeAlias;
    protected $_options;
    protected $_messages = array();

    public function __construct($attributeName, $options)
    {
        $this->_attributeName = $attributeName;
        $this->_options = $options;
    }

    public function setAttributeAlias($aliasName)
    {
        $this->_attributeAlias = $aliasName;
        return $this;
    }

    public function getMessages()
    {
        return $this->_messages;
    }
}