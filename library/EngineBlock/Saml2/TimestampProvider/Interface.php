<?php

interface EngineBlock_Saml2_TimestampProvider_Interface
{
    public function timestamp($deltaSeconds = 0, $time = null);
}
