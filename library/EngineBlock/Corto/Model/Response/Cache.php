<?php

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

class EngineBlock_Corto_Model_Response_Cache
{
    /**
     * Remember the IDP used to authenticate.
     *
     * Note that only the SP/IDP entity ID combination is stored, and not the
     * complete response. Responses are never re-purposed. This information is
     * only used to allow auto-selecting the IDP on subsequent logins.
     *
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse
     */
    public static function rememberIdp(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest,
        EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse
    ) {
        if (!isset($_SESSION['CachedResponses'])) {
            $_SESSION['CachedResponses'] = array();
        }

        $_SESSION['CachedResponses'][] = array(
            'sp'  => $receivedRequest->getIssuer(),
            'idp' => $receivedResponse->getIssuer(),
        );
    }

    /**
     * Find remembered IDP applicable for given SP.
     *
     * @param ServiceProvider $sp
     * @param array $scopedIdps
     * @return string|null
     */
    public static function findRememberedIdp(ServiceProvider $sp, array $scopedIdps)
    {
        $cachedResponses = [];
        if (isset($_SESSION['CachedResponses'])) {
            $cachedResponses = $_SESSION['CachedResponses'];
        }

        // First, if there is scoping, we reject responses from idps not in
        // the list.
        if (count($scopedIdps) > 0) {
            foreach ($cachedResponses as $key => $cachedResponse) {
                if (!in_array($cachedResponse['idp'], $scopedIdps)) {
                    unset($cachedResponses[$key]);
                }
            }
        }

        foreach ($cachedResponses as $cachedResponse) {
            // Check if it is for an allowed idp
            if (!$sp->isAllowed($cachedResponse['idp'])) {
                continue;
            }

            return $cachedResponse['idp'];
        }
    }
}
