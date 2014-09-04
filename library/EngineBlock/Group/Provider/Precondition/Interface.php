<?php

interface EngineBlock_Group_Provider_Precondition_Interface
{
    public function __construct(EngineBlock_Group_Provider_Interface $provider, Zend_Config $options);
    public function validate();
}