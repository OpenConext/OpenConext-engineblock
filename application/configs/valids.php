<?php

return array (
    'urn:mace:dir:attribute-def:uid' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'min' => '1',
                'max' => '1',
                'maxLength' => '256',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:sn' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:givenName' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:displayName' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:mail' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'validate' => 'EmailAddress',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonAffiliation' =>
    array (
        'Constraints' =>
        array (
            'caseInsensitive' => true,
            'error' => array(
                'allowed' => array('affiliate', 'alum', 'employee', 'student', 'staff'),
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonEntitlement' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'validate' => 'URI',
            )
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonPrincipalName' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
                'validateRegex' => '/[\\S]+@[\\S]+/',
            ),
            'caseInsensitive' => true,
        ),
    ),
    'urn:mace:dir:attribute-def:preferredLanguage' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:terena.org:attribute-def:schacHomeOrganization' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'min' => '1',
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:terena.org:attribute-def:schacHomeOrganizationType' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:surffederatie.nl:attribute-def:nlEduPersonHomeOrganization' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:surffederatie.nl:attribute-def:nlEduPersonOrgUnit' =>
        new stdClass(),
    'urn:mace:surffederatie.nl:attribute-def:nlEduPersonStudyBranch' =>
        new stdClass(),
    'urn:mace:surffederatie.nl:attribute-def:nlStudielinkNummer' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:surffederatie.nl:attribute-def:nlDigitalAuthorIdentifier' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonNickname' =>
    array (
        'Constraints' =>
        array (
            'caseInsensitive' => true,
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonOrgDN' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonOrgUnitDN' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:eduPersonPrimaryAffiliation' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
                'allowed' => array('affiliate', 'alum', 'employee', 'student', 'staff'),
            ),
            'caseInsensitive' => true,
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonPrimaryOrgUnitDN' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonScopedAffiliation' =>
    array (
        'Constraints' =>
        array (
            'caseInsensitive' => true,
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonTargetedID' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:eduPersonAssurance' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'validate' => 'URI',
            )
        ),
    ),
    'urn:mace:dir:attribute-def:audio' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:cn' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:description' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:facsimileTelephoneNumber' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:homePhone' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:homePostalAddress' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '6',
                'maxLength' => '30',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:initials' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:jpegPhoto' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:l' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:labeledURI' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:manager' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:mobile' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:o' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:ou' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:pager' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:postalAddress' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:postalCode' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:postOfficeBox' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:seeAlso' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:st' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:street' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:telephoneNumber' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:title' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:uniqueIdentifier' =>
    array (
        'Constraints' =>
        array (
            'error' =>
            array (
                'max' => '1',
            ),
        ),
    ),
    'urn:mace:dir:attribute-def:userCertificate' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:userPassword' =>
        new stdClass(),
    'urn:mace:dir:attribute-def:userSMIMECertificate' =>
        new stdClass(),
);