<?php

namespace OpenSamlPhp;

$entities = Metadata\EntitiesDescriptor::createFromFile('entities.xml');

$request = Http\Request::createFromEnvironment();

$hostedEntity = $entities->getEntityByAcsUrl($request->getUrl());

$binding = Binding\Factory::createFromHttpRequest($request);
$messageXml = $binding->getMessageXml();

$authNRequest = Request\Authn::createFromXml($messageXml);

$state = Proxy\SessionManager::create();
$state->Binding = $binding;
$state->Xml     = $messageXml;
$state->Request = $authNRequest;
$state->HostedEntity = $hostedEntity;

$issuer = $authNRequest->getIssuerEntityId();
$remoteSp = $remoteSps->getEntityById($issuer);

if ($hostedEntity->SPSSODescriptor->AuthnRequestSigned || $remoteSp->AuthnRequestSigned) {
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


