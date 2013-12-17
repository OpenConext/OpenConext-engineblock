<?php

class EngineBlock_Saml2_IdGenerator_Default implements EngineBlock_Saml2_IdGenerator_Interface
{
    public function generate($prefix = 'EB')
    {
        return  $prefix . sha1(uniqid(mt_rand(), true));
    }
}
