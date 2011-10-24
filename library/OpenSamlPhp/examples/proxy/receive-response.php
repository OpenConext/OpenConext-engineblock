<?php

// Send response

$inResponseTo = $response->getInResponseTo();

if ($inResponseTo) {
    $session = Proxy\SessionManager::create($inResponseTo);
}