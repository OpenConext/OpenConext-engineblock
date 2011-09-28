<?php

namespace OpenSamlPhp;

$request = new Request\Authn;
$request->setAssertionConsumerServiceUrl()
        ->setDestination()
        ->generateId()
        ->setIssueInstant()

        ->setIssuer("")
        ->setNameIDPolicy()
        ->setProviderName("MySP")

        ->sign();

$binding = new Binding\HttpRedirect(Binding\HttpRedirect::MESSAGE_KEY_REQUEST);
$binding->send();