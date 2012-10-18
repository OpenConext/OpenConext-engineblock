1 urn:mace:dir:attribute-def:uid
Values must be no longer than 256 characters.

0..1 urn:mace:dir:attribute-def:sn

0..1 urn:mace:dir:attribute-def:givenName

0..1 urn:mace:dir:attribute-def:displayName

0..* urn:mace:dir:attribute-def:mail
#Values SHOULD match RFC2821
Values MUST be a valid EmailAddress

0..* urn:mace:dir:attribute-def:eduPersonAffiliation
Values are case insensitive.
Allowed values:
* affiliate
* alum
* employee
* student
* staff

0..* urn:mace:dir:attribute-def:eduPersonEntitlement
Values MUST be URIs.

0..1 urn:mace:dir:attribute-def:eduPersonPrincipalName
Values are case insensitive.
Value MUST match /[\S]+@[\S]+/

0..1 urn:mace:dir:attribute-def:preferredLanguage
Values MUST match /[A-Z]{2}/

1 urn:mace:terena.org:attribute-def:schacHomeOrganization
Value MUST be a valid HostName

0..1 urn:mace:terena.org:attribute-def:schacHomeOrganizationType
Allowed values:
* urn:mace:terena.org:schac:homeOrganizationType:eu:higherEducationInstitution
* urn:mace:terena.org:schac:homeOrganizationType:eu:educationInstitution
* urn:mace:terena.org:schac:homeOrganizationType:int:NREN
* urn:mace:terena.org:schac:homeOrganizationType:int:universityHospital
* urn:mace:terena.org:schac:homeOrganizationType:int:NRENAffiliate
* urn:mace:terena.org:schac:homeOrganizationType:int:other

0..1 urn:mace:surffederatie.nl:attribute-def:nlEduPersonHomeOrganization [DEPRECATED]

0..* urn:mace:surffederatie.nl:attribute-def:nlEduPersonOrgUnit

0..* urn:mace:surffederatie.nl:attribute-def:nlEduPersonStudyBranch
# CROHO code?

0..1 urn:mace:surffederatie.nl:attribute-def:nlStudielinkNummer
# Studielink code?

0..1 urn:mace:surffederatie.nl:attribute-def:nlDigitalAuthorIdentifier
# DAI code?

## EduPerson 200806 schema ##

0..* urn:mace:dir:attribute-def:eduPersonNickname
Values are case insensitive.

0..1 urn:mace:dir:attribute-def:eduPersonOrgDN
# Value MUST be a DN

0..* urn:mace:dir:attribute-def:eduPersonOrgUnitDN
# Values MUST be DNs

0..1 urn:mace:dir:attribute-def:eduPersonPrimaryAffiliation
Values are case insensitive.
Allowed values:
* faculty
* student
* staff
* alum
* member
* employee
* library-walk-in

0..1 urn:mace:dir:attribute-def:eduPersonPrimaryOrgUnitDN
# Value MUST be a DN

0..* urn:mace:dir:attribute-def:eduPersonScopedAffiliation
Values are case insensitive.
Values must match /(faculty|student|staff|alum|member|employee|library-walk-in)@[\S]+/

# Note: which syntax should we require of IdPs? The original (200806) MACE or the SAML NameID kind?
# See also: https://wiki.surfnetlabs.nl/display/conextdocumentation/Technical+RFC+-+eduPersonTargetedID+should+contain+the+SAML+NameID
0..* urn:mace:dir:attribute-def:eduPersonTargetedID

0..* urn:mace:dir:attribute-def:eduPersonAssurance
Values MUST be URIs.

0..* urn:mace:dir:attribute-def:audio [DEPRECATED]

0..* urn:mace:dir:attribute-def:cn

0..* urn:mace:dir:attribute-def:description

0..* urn:mace:dir:attribute-def:facsimileTelephoneNumber
#Values SHOULD match ITU E.123: http://www.itu.int/rec/T-REC-E.123-200102-I/en

0..* urn:mace:dir:attribute-def:homePhone
#Values SHOULD match ITU E.123: http://www.itu.int/rec/T-REC-E.123-200102-I/en

0..6 urn:mace:dir:attribute-def:homePostalAddress
Values must be no longer than 30 characters.

0..* urn:mace:dir:attribute-def:initials

0..* urn:mace:dir:attribute-def:jpegPhoto
#Values SHOULD match JPEG File Interchange Format

0..* urn:mace:dir:attribute-def:l

0..* urn:mace:dir:attribute-def:labeledURI
#Values SHOULD be a URI followed by a label



0..* urn:mace:dir:attribute-def:manager
# Values MUST be DNs

0..* urn:mace:dir:attribute-def:mobile
#Values SHOULD match ITU E.123: http://www.itu.int/rec/T-REC-E.123-200102-I/en

0..* urn:mace:dir:attribute-def:o

0..* urn:mace:dir:attribute-def:ou

0..* urn:mace:dir:attribute-def:pager
#Values SHOULD match ITU E.123: http://www.itu.int/rec/T-REC-E.123-200102-I/en

0..* urn:mace:dir:attribute-def:postalAddress
0..* urn:mace:dir:attribute-def:postalCode
0..* urn:mace:dir:attribute-def:postOfficeBox


0..* urn:mace:dir:attribute-def:seeAlso
# Values MUST be DNs

0..* urn:mace:dir:attribute-def:st
0..* urn:mace:dir:attribute-def:street
0..* urn:mace:dir:attribute-def:telephoneNumber
#Values SHOULD match ITU E.123: http://www.itu.int/rec/T-REC-E.123-200102-I/en

0..* urn:mace:dir:attribute-def:title



0..1 urn:mace:dir:attribute-def:uniqueIdentifier
0..* urn:mace:dir:attribute-def:userCertificate
0..* urn:mace:dir:attribute-def:userPassword
# Values SHOULD be in format: {encryption method}encrypted password.
0..* urn:mace:dir:attribute-def:userSMIMECertificate
0..* urn:mace:dir:attribute-def:x500uniqueIdentifier
