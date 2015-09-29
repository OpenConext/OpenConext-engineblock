<?php

class EngineBlock_Corto_Model_Response_Cache
{
    const RESPONSE_CACHE_TYPE_IN  = 'in';

    public static function cacheResponse(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest,
        EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse,
        $type
    ) {
        if ($type !== self::RESPONSE_CACHE_TYPE_IN) {
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
    }
}
