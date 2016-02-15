<?php

interface EngineBlock_Saml2_IdGenerator_Interface
{
    public function generate($prefix = 'EB', $usage = \OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\IdFrame::ID_USAGE_OTHER);
}
