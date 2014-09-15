<?php

interface EngineBlock_Attributes_Validator_Interface
{
    public function __construct($attributeName, $options);
    public function validate(array $attributes);
    public function setAttributeAlias($alias);
    public function getMessages();
}