<?php

class EngineBlock_Corto_Model_Response_Cache
{
    const RESPONSE_CACHE_TYPE_IN  = 'in';
    const RESPONSE_CACHE_TYPE_OUT = 'out';

    public static function cacheResponse(array $receivedRequest, array $receivedResponse, $type, $voContext = "")
    {
        $requestIssuerEntityId  = $receivedRequest['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];
        $responseIssuerEntityId = $receivedResponse['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];
        if (!isset($_SESSION['CachedResponses'])) {
            $_SESSION['CachedResponses'] = array();
        }
        $_SESSION['CachedResponses'][] = array(
            'sp'            => $requestIssuerEntityId,
            'idp'           => $responseIssuerEntityId,
            'type'          => $type,
            'response'      => $receivedResponse,
            'vo'            => $voContext,
        );
        return $_SESSION['CachedResponses'][count($_SESSION['CachedResponses']) - 1];
    }
}