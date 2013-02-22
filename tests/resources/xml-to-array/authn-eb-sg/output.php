<?php
return array (
    '__t' => 'samlp:AuthnRequest',
    '_ID' => 'CORTOb9f5053bdbc5f773b6e080bd029e3b1a55eb90ca',
    '_Version' => '2.0',
    '_IssueInstant' => '2013-01-29T09:00:33Z',
    '_Destination' => 'https://espee.surfnet.nl/federate/saml20/https%253A%252F%252Fsurfguest.nl',
    '_ForceAuthn' => 'false',
    '_IsPassive' => 'false',
    '_AssertionConsumerServiceURL' => 'https://engine.surfconext.nl/authentication/sp/consume-assertion',
    '_ProtocolBinding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
    'saml:Issuer' =>
    array (
        '__v' => 'https://engine.surfconext.nl/authentication/sp/metadata',
    ),
    'samlp:NameIDPolicy' =>
    array (
        '_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
        '_AllowCreate' => 'true',
    ),
    'samlp:Scoping' =>
    array (
        '_ProxyCount' => '10',
        'samlp:RequesterID' =>
        array (
            0 =>
            array (
                '__v' => 'https://profile.surfconext.nl/simplesaml/module.php/saml/sp/metadata.php/default-sp',
            ),
        ),
    ),
);