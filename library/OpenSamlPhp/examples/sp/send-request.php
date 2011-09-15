<?php

namespace OpenSamlPhp;
use OpenSamlPhp\Request\Authn\Scoping\IdpList as idpListNs;

$request = new Request\Authn();
$request->Scoping->IdpList->addIdpEntry(new idpListNs\IdpEntry('SURFguests'));
