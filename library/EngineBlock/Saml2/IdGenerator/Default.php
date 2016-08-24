<?php

class EngineBlock_Saml2_IdGenerator_Default implements EngineBlock_Saml2_IdGenerator
{
    public function generate($prefix = 'EB', $usage = EngineBlock_Saml2_IdGenerator::ID_USAGE_OTHER)
    {
        return  $prefix . sha1(uniqid(mt_rand(), true));
    }
}
