<?php

namespace OpenSamlPhp;

require 'settings.inc.php';

$request = Http\Request::createFromEnvironment();

$hostedEntity = $hostedEntities->getEntityByAcsUrl($request->getUrl());

$binding = Binding\Factory::createFromHttpRequest($request);
$messageXml = $binding->getMessageXml();

$authNRequest = Request\Authn::createFromXml($messageXml);

session_start();
$_SESSION['SAMLRequest']['Origin'] = array(
    'Binding' => $binding,
    'Xml' => $messageXml,
    'Request' => $authNRequest,
    'HostedEntity' => $hostedEntity,
);

$sessionManager = new Proxy\SessionManager();
$state = $sessionManager->start($authNRequest->ID);
$state->Binding = $binding;
$state->Xml     = $messageXml;
$state->Request = $authNRequest;
$state->HostedEntity = $hostedEntity;

$issuer = $authNRequest->getIssuerEntityId();
$remoteSp = $remoteSps->getEntityById($issuer);

if ($hostedEntity->AuthnRequestSigned || $remoteSp->AuthnRequestSigned) {
    if (!$authNRequest->isSigned()) {
        throw new Proxy\Exception\UnexpectedUnsignedRequest;
    }
    try {
        $authNRequest->verifySignature();
    }
    catch (\OpenSamlPhp\Request\Exception\DigestMismatch $e) {
        die("Digest '{$e->receivedDigest}' did not match expected '{$e->expectedDigest}'");
    }
    catch (\OpenSamlPhp\Request\Exception\SignatureMismatch $e) {
        die("Signature Mismatch");
    }
}

$scoping = array_merge(
    $authNRequest->Scoping->IdpList->getEntityIds(),
    $remoteSp->Extensions->Defaults->AuthnRequest->Scoping->IdpList->getEntityIds()
);


