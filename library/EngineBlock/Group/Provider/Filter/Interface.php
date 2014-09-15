<?php

interface EngineBlock_Group_Provider_Filter_Interface
{
    public function __construct(Zend_Config $options);
    
    public function filter($data);
}
