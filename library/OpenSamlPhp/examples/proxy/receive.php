<?php

namespace OpenSamlPhp;

$request = Http\Request::createFromEnvironment();
$binding = Binding\Factory::createFromHttpRequest($request);
$messageXml = $binding->getMessageXml();

$authNRequest = Request\Authn::createFromXml($messageXml);

$issuer = $authNRequest->getIssuer();

if ($authNRequest->isSigned()) {
    try {
        $authNRequest->verifySignature();
    }
    catch (Request\Exception\SignatureMismatch $e) {
        die("Signature Mismatch");
    }
}

if (isset($authNRequest->scoping->idpList)) {
    if (isset($authNRequest->scoping->idpList->getComplete)) {
        $authNRequest->scoping->idpList->complete();
    }
}

