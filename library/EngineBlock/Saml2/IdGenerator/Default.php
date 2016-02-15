<?php

class EngineBlock_Saml2_IdGenerator_Default implements EngineBlock_Saml2_IdGenerator_Interface
{
    public function generate($prefix = 'EB', $usage = \OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\IdFrame::ID_USAGE_OTHER)
    {
        return  $prefix . sha1(uniqid(mt_rand(), true));
    }
}
