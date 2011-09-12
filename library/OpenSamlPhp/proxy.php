<?php

/**
 * Transform HTTP request and configuration to HTTP response
 *      Transform HTTP request to SAML request
 *      Transform SAML request to SAML response
 *          Transform SAML request to SAML array
 *          Transform SAML request array to SAML array response
 */

function singleSignOnAction($httpRequest, $config, $storage)
{
    $samlRequest = httpRequestToSamlRequest($httpRequest, $config);
    $samlRequest = scopeSamlRequestByVo($samlRequest, $httpRequest);
    $samlRequest = scopeSamlRequestByIdpHash($samlRequest, $httpRequest);
    $samlResponse = samlRequestToSamlResponse($samlRequest);
    return samlResponseToSamlRequest($samlResponse, $config);
}

function httpRequestToSamlRequest($httpRequest, $config) {
    if ($httpRequest->isPost()) {
        if (!$httpRequest->hasPostParam('SAMLRequest')) {
            return "NO_REQUEST";
        }

        return encodedSamlStringToSamlRequest(
            $httpRequest->getPostParam('SAMLRequest')
        );
    }
    else if ($httpRequest->isGet()) {
        if (!$httpRequest->hasQueryParam('SAMLRequest')) {
            return "NO_REQUEST";
        }
    }

    return null;
}

function encodedSamlStringToSamlRequest($samlRequestEncoded)
{
    $samlRequest =  base64_decode($samlRequestEncoded);
    $samlRequestJsonDecoded = json_decode($samlRequest);
    if ($samlRequestJsonDecoded) {
        return $samlRequestJsonDecoded;
    }
    else {
        $samlRequestDeflated = gzinflate($samlRequest);
        return samlXmlStringToSamlArray($samlRequestDeflated);
    }
}

function samlXmlStringToSamlArray($samlXmlString)
{

}

