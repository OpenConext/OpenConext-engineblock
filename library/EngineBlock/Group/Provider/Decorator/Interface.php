<?php

interface EngineBlock_Group_Provider_Decorator_Interface
{
    public static function createFromConfigsWithProvider(EngineBlock_Group_Provider_Interface $provider, Zend_Config $config);
}