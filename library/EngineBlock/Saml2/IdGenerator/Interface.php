<?php

interface EngineBlock_Saml2_IdGenerator_Interface
{
    public function generate($prefix = 'EB', $usage = \OpenConext\Component\EngineBlockFixtures\IdFrame::ID_USAGE_OTHER);
}
