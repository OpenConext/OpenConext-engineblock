<?php

namespace OpenSamlPhp;

$request = Http\Request::createFromEnvironment();
$binding = Binding\Factory::createFromHttpRequest($request);
$messageXml = $binding->getMessageXml();

$authNRequest = Request\Authn::createFromXml($messageXml);

$issuer = $authNRequest->getIssuerEntityId();

if ($authNRequest->isSigned()) {
    try {
        $authNRequest->verifySignature();
    }
    catch (Request\Exception\SignatureMismatch $e) {
        die("Signature Mismatch");
    }
}


