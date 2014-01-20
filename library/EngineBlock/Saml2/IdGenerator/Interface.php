<?php

interface EngineBlock_Saml2_IdGenerator_Interface
{
    const ID_USAGE_SAML2_RESPONSE   = 'saml2-response';
    const ID_USAGE_SAML2_REQUEST    = 'saml2-request';
    const ID_USAGE_SAML2_ASSERTION  = 'saml2-assertion';
    const ID_USAGE_SAML2_METADATA   = 'saml2-metadata';
    const ID_USAGE_OTHER            = 'other';

    public function generate($prefix = 'EB', $usage = self::ID_USAGE_OTHER);
}
