<?php
/**
 * Collection of urn constants to prevent having them like brittle 'magic strings' in code
 */
class EngineBlock_Urn
{
    const SAML1_1_NAMEID_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
    // @todo add 1.1. emailAddress, if so, what should EB do with it?
    const SAML2_0_NAMEID_FORMAT_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
    const SAML2_0_NAMEID_FORMAT_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';

    /**
     * @deprecated This is an incorrect name id format since unspecified does no longer exist in SAML 2.0, only use this for backwards compatibility
     */
    const SAML2_0_NAMEID_FORMAT_UNSPECIFIED = 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified';
}
