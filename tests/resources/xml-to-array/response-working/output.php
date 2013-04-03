<?php
return array (
    '__t' => 'samlp:Response',
    '_ID' => '_a6b2c192b7f715a84b0a4237251c3ebe56028a4414',
    '_InResponseTo' => 'CORTOea1ef358da695ef0e9357626dd93071dd89848d7',
    '_Version' => '2.0',
    '_IssueInstant' => '2013-01-10T08:48:14Z',
    '_Destination' => 'https://engine.surfconext.nl/authentication/sp/consume-assertion',
    'saml:Issuer' =>
    array (
        '__v' => 'https://surfguest.nl',
    ),
    'samlp:Status' =>
    array (
        'samlp:StatusCode' =>
        array (
            '_Value' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
        ),
    ),
    'saml:Assertion' =>
    array (
        '_Version' => '2.0',
        '_ID' => 'pfxbbc4f790-d52b-4221-fdf3-21269c285de0',
        '_IssueInstant' => '2013-01-10T08:48:14Z',
        'saml:Issuer' =>
        array (
            '__v' => 'https://surfguest.nl',
        ),
        'ds:Signature' =>
        array (
            'ds:SignedInfo' =>
            array (
                'ds:CanonicalizationMethod' =>
                array (
                    '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                ),
                'ds:SignatureMethod' =>
                array (
                    '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#rsa-sha1',
                ),
                'ds:Reference' =>
                array (
                    0 =>
                    array (
                        '_URI' => '#pfxbbc4f790-d52b-4221-fdf3-21269c285de0',
                        'ds:Transforms' =>
                        array (
                            'ds:Transform' =>
                            array (
                                0 =>
                                array (
                                    '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#enveloped-signature',
                                ),
                                1 =>
                                array (
                                    '_Algorithm' => 'http://www.w3.org/2001/10/xml-exc-c14n#',
                                ),
                            ),
                        ),
                        'ds:DigestMethod' =>
                        array (
                            '_Algorithm' => 'http://www.w3.org/2000/09/xmldsig#sha1',
                        ),
                        'ds:DigestValue' =>
                        array (
                            '__v' => '3kqZA8wvtqv1INYTTR5F6o/cO90=',
                        ),
                    ),
                ),
            ),
            'ds:SignatureValue' =>
            array (
                '__v' => 'K0SN9f7NiBkCXh7A+u1WdsS61LkqhLy8eD0DKunpi1N82IJZP8H4i1nM/+q0hCmth4oVf53R/o9lb8CriYNxAzBIesGrWFvZYoRTG1XliAR3e0eeSI8cQXwRMdRLM1OZfBlmK1S9wfvkgX50zZL654Bstj1RTistutuUQUqRkH8=',
            ),
        ),
        'saml:Subject' =>
        array (
            'saml:NameID' =>
            array (
                '_Format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
                '__v' => 'remold@surfguest.nl',
            ),
            'saml:SubjectConfirmation' =>
            array (
                '_Method' => 'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                'saml:SubjectConfirmationData' =>
                array (
                    '_NotOnOrAfter' => '2013-01-10T08:53:14Z',
                    '_InResponseTo' => 'CORTOea1ef358da695ef0e9357626dd93071dd89848d7',
                    '_Recipient' => 'https://engine.surfconext.nl/authentication/sp/consume-assertion',
                ),
            ),
        ),
        'saml:Conditions' =>
        array (
            '_NotBefore' => '2013-01-10T08:47:44Z',
            '_NotOnOrAfter' => '2013-01-10T08:53:14Z',
            'saml:AudienceRestriction' =>
            array (
                'saml:Audience' =>
                array (
                    '__v' => 'https://engine.surfconext.nl/authentication/sp/metadata',
                ),
            ),
        ),
        'saml:AuthnStatement' =>
        array (
            '_AuthnInstant' => '2013-01-10T08:48:14Z',
            '_SessionIndex' => '4c9d75576aedba536e0868182f2aaede',
            'saml:AuthnContext' =>
            array (
                'saml:AuthnContextClassRef' =>
                array (
                    '__v' => 'urn:oasis:names:tc:SAML:2.0:ac:classes:Password',
                ),
            ),
        ),
        'saml:AttributeStatement' =>
        array (
            0 =>
            array (
                'saml:Attribute' =>
                array (
                    0 =>
                    array (
                        '_Name' => 'urn:mace:dir:attribute-def:uid',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'remold',
                            ),
                        ),
                    ),
                    1 =>
                    array (
                        '_Name' => 'urn:mace:dir:attribute-def:cn',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'Remold Krol',
                            ),
                        ),
                    ),
                    2 =>
                    array (
                        '_Name' => 'urn:mace:dir:attribute-def:givenName',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'Remold',
                            ),
                        ),
                    ),
                    3 =>
                    array (
                        '_Name' => 'urn:mace:dir:attribute-def:sn',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'Krol',
                            ),
                        ),
                    ),
                    4 =>
                    array (
                        '_Name' => 'urn:mace:dir:attribute-def:displayName',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'Remold Krol',
                            ),
                        ),
                    ),
                    5 =>
                    array (
                        '_Name' => 'urn:mace:dir:attribute-def:mail',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'remold@everett.nl',
                            ),
                        ),
                    ),
                    6 =>
                    array (
                        '_Name' => 'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'surfguest.nl',
                            ),
                        ),
                    ),
                    7 =>
                    array (
                        '_Name' => 'urn:mace:dir:attribute-def:eduPersonPrincipalName',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'remold@SURFguest.nl',
                            ),
                        ),
                    ),
                    8 =>
                    array (
                        '_Name' => 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'guest',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);