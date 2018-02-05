<?php

use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;

abstract class EngineBlock_Attributes_Validator_Abstract implements EngineBlock_Attributes_Validator_Interface
{
    /**
     * @var string
     */
    protected $_attributeName;

    /**
     * @var string
     */
    protected $_attributeAlias;

    /**
     * @var mixed
     */
    protected $_options;

    /**
     * @var array
     */
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
