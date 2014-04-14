<?php

class EngineBlock_Corto_Model_Response_Cache
{
    const RESPONSE_CACHE_TYPE_IN  = 'in';
    const RESPONSE_CACHE_TYPE_OUT = 'out';

    public static function cacheResponse(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest,
        EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse,
        $type
    ) {
        if (!in_array($type, array(self::RESPONSE_CACHE_TYPE_IN, self::RESPONSE_CACHE_TYPE_OUT))) {
            throw new EngineBlock_Exception('Unknown response type');
        }

        if (!isset($_SESSION['CachedResponses'])) {
            $_SESSION['CachedResponses'] = array();
        }
        $_SESSION['CachedResponses'][] = array(
            'sp'            => $receivedRequest->getIssuer(),
            'idp'           => $receivedResponse->getIssuer(),
            'type'          => $type,
            'response'      => $receivedResponse,
            'vo'            => $receivedRequest->getVoContext(),
            'key'           => $receivedRequest->getKeyId(),
        );
        return $_SESSION['CachedResponses'][count($_SESSION['CachedResponses']) - 1];
    }
}
