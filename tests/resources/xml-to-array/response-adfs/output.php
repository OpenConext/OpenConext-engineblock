<?php
return array (
    '__t' => 'samlp:Response',
    '_ID' => '_2ccd45db-3fdc-45c7-98ca-be7b4a16d287',
    '_Version' => '2.0',
    '_IssueInstant' => '2013-01-10T10:42:29.194Z',
    '_Destination' => 'https://engine.test.surfconext.nl/authentication/sp/consume-assertion',
    '_Consent' => 'urn:oasis:names:tc:SAML:2.0:consent:unspecified',
    '_InResponseTo' => 'CORTO8f7159267a98a60f3c821b13a356a64269674a0d',
    'saml:Issuer' =>
    array (
        '__v' => 'http://ad.w2k8.test.localdomain/adfs/services/trust',
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
        '_ID' => '_9b963c6e-dc69-4b2a-80d5-00f4d9082960',
        '_IssueInstant' => '2013-01-10T10:42:29.194Z',
        '_Version' => '2.0',
        'saml:Issuer' =>
        array (
            '__v' => 'http://ad.w2k8.test.localdomain/adfs/services/trust',
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
                        '_URI' => '#_9b963c6e-dc69-4b2a-80d5-00f4d9082960',
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
                            '__v' => 'D7XerHtkVCZZB5ZWE4/oqMe8rm8=',
                        ),
                    ),
                ),
            ),
            'ds:SignatureValue' =>
            array (
                '__v' => 'bW3kNLEd1eBoc4q55c/yiCbN6cxAWfa0IdUuD4TD5E6hNDbNRFMCnxNr2pv0y0f06sNZCZ2apoyYXPkJPImd+t4sIr9ntAn36rn+d74taT65aWOJ224cZ9Bmgl+eaBWdxIrfSg6TVoprj/c/I3/+Brxy4Saz0SdvV/aT72tUFEOV99uaWHNfYAP66Okf0uHduB1finyKvYNl/kfGwEPGX5LqAdNwy3DSBfvv9oG43OfK2HUwBBg571pHzhRABFg8e4w8MXrQPlnTlQQZ7rvmuf4KXnp7J8gZvAY13o1aPBuHTSLP0Yidqng7zhx4Eu6i4t1agVzIvqVoA9jYyKB6Pw==',
            ),
            'ds:KeyInfo' =>
            array (
                'ds:X509Data' =>
                array (
                    0 =>
                    array (
                        'ds:X509Certificate' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'MIIC7DCCAdSgAwIBAgIQMnLa3TMbHa1FyPMboZeMCDANBgkqhkiG9w0BAQsFADAyMTAwLgYDVQQDEydBREZTIFNpZ25pbmcgLSBhZC53Mms4LnRlc3QubG9jYWxkb21haW4wHhcNMTIxMTA1MTEwMzA1WhcNMTMxMTA1MTEwMzA1WjAyMTAwLgYDVQQDEydBREZTIFNpZ25pbmcgLSBhZC53Mms4LnRlc3QubG9jYWxkb21haW4wggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDBfTZLFMSIq6R6UGBOu7kCYmDDvfFYum5vCPu8DQk5jzNLJrqgEHuKqlteUGqCaNpjmw5WRZrn2zQgW0C9PqwSinKro9AVAbpvDzICMNLl3R2G/dfkqMIx4jZ3NRvBY5tA3aJU17apXWqGm8oYW3GRtP5wi1n6OSLKWqyoaMUKf1xa5+D3TAjOvsUxYyZtgMrqq3SXFofDzHqHd02T386TMQTR47nMozuS5zdCoxzE84+1Sl0PTHiwGPVYER6qM7UA3yTOBjEaDlStc0YL3Not3i2VVoqVC7AInCg2KP1mamdhYLdN1NKIhEsrd/fXeLipZWuhzqPa0bbezJ3OkZ9JAgMBAAEwDQYJKoZIhvcNAQELBQADggEBALjgxi5Hxv2/SgWz+taLkVbfgI1tZ5ILmFO9O2d/qpJNgohIPVPW2IcAQGGFstxIixegf7+KTxnLzZ3C7jRt8xC3NznOuQNvvcfwqzwQ3hDYlFzPmQXNP39jRO7Cpuef8+Y0trUSiKeTuUD94WwlbwXuQftnVgIMqla4EIjxWuWk6IVLXDQXL9ZnYdEjc1NjEWD4l29mhOpkOdqOMq33Sk4SP98nZnbQHj5Ir2ke6xAagNogwdRksifikBFwebX9xaYCLSGXj0tRO0dLVbXq1zo3+9deE8fVo1pDQ+mJhzLbh7ZZE46hTVtLgVXQrjjZrytyce4ebfTR6HcAVo4dOkg=',
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'saml:Subject' =>
            array (
                'saml:NameID' =>
                array (
                        '__v' => 'Administrator@w2k8.test.localdomain',
                ),
                'saml:SubjectConfirmation' =>
                array (
                        '_Method' => 'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                        'saml:SubjectConfirmationData' =>
                        array (
                                '_InResponseTo' => 'CORTO8f7159267a98a60f3c821b13a356a64269674a0d',
                                '_NotOnOrAfter' => '2013-01-10T10:47:29.194Z',
                                '_Recipient' => 'https://engine.test.surfconext.nl/authentication/sp/consume-assertion',
                        ),
                ),
            ),
        'saml:Conditions' =>
            array (
                '_NotBefore' => '2013-01-10T10:42:29.179Z',
                '_NotOnOrAfter' => '2013-01-10T11:42:29.179Z',
                'saml:AudienceRestriction' =>
                array (
                        'saml:Audience' =>
                            array (
                                '__v' => 'https://engine.test.surfconext.nl/authentication/sp/metadata',
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
                        '_Name' => 'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'demo.nl',
                            ),
                        ),
                    ),
                    1 =>
                    array (
                        '_Name' => 'urn:mace:dir:attribute-def:uid',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'Administrator',
                            ),
                        ),
                    ),
                    2 =>
                    array (
                        '_Name' => 'urn:mace:dir:attribute-def:mail',
                        'saml:AttributeValue' =>
                        array (
                            0 =>
                            array (
                                '__v' => 'Administrator@w2k8.test.localdomain',
                            ),
                        ),
                    ),
                ),
            ),
        ),
        'saml:AuthnStatement' =>
            array (
                '_AuthnInstant' => '2013-01-10T10:42:29.069Z',
                '_SessionIndex' => '_9b963c6e-dc69-4b2a-80d5-00f4d9082960',
                'saml:AuthnContext' =>
                    array (
                        'saml:AuthnContextClassRef' =>
                        array (
                                '__v' => 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport',
                            ),
                ),
            ),
        ),

);