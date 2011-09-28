<?php

/**
 * PHP Array with URN identifiers for attributes with corresponding names and descriptions in IETF language tag format.
 *
 * @var array $attributes
 */
$attributes = array (
  'urn:mace:dir:attribute-def:uid' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'UID',
      'en_US' => 'UID',
    ),
    'Description' =>
    array (
      'nl_NL' => 'jouw unieke gebruikersnaam binnen jouw instelling',
      'en_US' => 'your unique username within your organization',
    ),
  ),
  'urn:mace:dir:attribute-def:sn' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Achternaam',
      'en_US' => 'Surname',
    ),
    'Description' =>
    array (
      'nl_NL' => 'jouw achternaam',
      'en_US' => 'your surname',
    ),
  ),
  'urn:mace:dir:attribute-def:givenName' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Voornaam',
      'en_US' => 'Name',
    ),
    'Description' =>
    array (
      'nl_NL' => 'voornaam/roepnaam',
      'en_US' => 'your name',
    ),
  ),
  'urn:mace:dir:attribute-def:cn' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Volledige persoonsnaam',
      'en_US' => 'Full Name',
    ),
    'Description' =>
    array (
      'nl_NL' => 'volledige persoonsnaam',
      'en_US' => 'your full name',
    ),
  ),
  'urn:mace:dir:attribute-def:displayName' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Weergavenaam',
      'en_US' => 'Display Name',
    ),
    'Description' =>
    array (
      'nl_NL' => 'weergave naam zoals getoond in applicaties',
      'en_US' => 'display name as shown in applications',
    ),
  ),
  'urn:mace:dir:attribute-def:mail' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'E-mailadres',
      'en_US' => 'E-mailaddress',
    ),
    'Description' =>
    array (
      'nl_NL' => 'jouw e-mailadres zoals bekend binnen jouw instelling',
      'en_US' => 'your e-mailaddress as known within your organization',
    ),
  ),
  'urn:mace:dir:attribute-def:eduPersonAffiliation' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Relatie',
      'en_US' => 'Relation',
    ),
    'Description' =>
    array (
      'nl_NL' => 'geeft de relatie aan tussen jou en jouw instelling',
      'en_US' => 'relation between your and your organization',
    ),
  ),
  'urn:mace:dir:attribute-def:eduPersonEntitlement' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Rechtaanduiding',
      'en_US' => 'Entitlement',
    ),
    'Description' =>
    array (
      'nl_NL' => 'rechtaanduiding; URI (URL of URN) dat een recht op iets aangeeft; wordt bepaald door een contract tussen dienstaanbieder en instelling',
      'en_US' => 'entitlement which decides upon your authorization within the application',
    ),
  ),
  'urn:mace:dir:attribute-def:eduPersonPrincipalName' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Net-ID',
      'en_US' => 'Net-ID',
    ),
    'Description' =>
    array (
      'nl_NL' => 'jouw unieke gebruikersnaam binnen jouw instelling aangevuld met "@instellingsnaam.nl"',
      'en_US' => 'your unique username within your organization augmented with "@organizationname.nl"',
    ),
  ),
  'urn:mace:dir:attribute-def:preferredLanguage' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Voorkeurstaal',
      'en_US' => 'Preferred Language',
    ),
    'Description' =>
    array (
      'nl_NL' => 'een tweeletterige afkorting van de voorkeurstaal volgens de ISO 639 taalafkortings codetabel; geen subcodes',
      'en_US' => 'a two letter abbreviation according to ISO 639; no subcodes',
    ),
  ),
  'urn:mace:terena.org:attribute-def:schacHomeOrganization' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Organisatie',
      'en_US' => 'Organization',
    ),
    'Description' =>
    array (
      'nl_NL' => 'aanduiding voor de organisatie van een persoon gebruikmakend van de domeinnaam van de organisatie; syntax conform RFC 1035',
      'en_US' => 'name for the organization, making use of the domain name of the  organization conform RFC 1035',
    ),
  ),
  'urn:mace:terena.org:attribute-def:schacHomeOrganizationType' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Type Organisatie',
      'en_US' => 'Type of Organization',
    ),
    'Description' =>
    array (
      'nl_NL' => 'aanduiding voor het type organisatie waartoe een persoon behoort, gebruikmakend van de waarden zoals geregisteerd door Terena op: http://www.terena.org/registry/terena.org/schac/homeOrganizationType',
      'en_US' => 'type of organization to which the user belongs',
    ),
  ),
  'urn:mace:surffederatie.nl:attribute-def:nlEduPersonHomeOrganization' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Weergavenaam van de Instelling',
      'en_US' => 'Display name of Organization',
    ),
    'Description' =>
    array (
      'nl_NL' => 'weergavenaam van de instelling',
      'en_US' => 'display name of the organization',
    ),
  ),
  'urn:mace:surffederatie.nl:attribute-def:nlEduPersonOrgUnit' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Afdelingsnaam',
      'en_US' => 'Unitname',
    ),
    'Description' =>
    array (
      'nl_NL' => ' naam van de afdeling',
      'en_US' => 'unit name',
    ),
  ),
  'urn:mace:surffederatie.nl:attribute-def:nlEduPersonStudyBranch' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Opleiding',
      'en_US' => 'Study Branch',
    ),
    'Description' =>
    array (
      'nl_NL' => 'opleiding; numerieke string die de CROHOcode bevat. leeg als het een niet reguliere opleiding betreft',
      'en_US' => 'study branch; numeric string which contains the CROHOcode. can be empty if the branch is unknown',
    ),
  ),
  'urn:mace:surffederatie.nl:attribute-def:nlStudielinkNummer' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Studielinknummer',
      'en_US' => 'Studielinknummer',
    ),
    'Description' =>
    array (
      'nl_NL' => 'studielinknummer van student zoals geregistreerd bij www.studielink.nl',
      'en_US' => 'studielinknummer of the student as registered at www.studielink.nl',
    ),
  ),
  'urn:mace:surffederatie.nl:attribute-def:nlDigitalAuthorIdentifier' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'DAI',
      'en_US' => 'DAI',
    ),
    'Description' =>
    array (
      'nl_NL' => 'Digital Author Identifier (DAI) zoals beschreven op: http://www.surffoundation.nl/smartsite.dws?ch=eng&id=13480',
      'en_US' => 'Digital Author Identifier (DAI) as described at: http://www.surffoundation.nl/smartsite.dws?ch=eng&id=13480',
    ),
  ),
  'urn:mace:surffederatie_nl:attribute-def:nlEduPersonHomeOrganization' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Weergavenaam van de Instelling',
      'en_US' => 'Display name of Organization',
    ),
    'Description' =>
    array (
      'nl_NL' => 'weergavenaam van de instelling',
      'en_US' => 'display name of the organization',
    ),
  ),
  'urn:mace:surffederatie_nl:attribute-def:nlEduPersonOrgUnit' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Afdelingsnaam',
      'en_US' => 'Unitname',
    ),
    'Description' =>
    array (
      'nl_NL' => ' naam van de afdeling',
      'en_US' => 'unit name',
    ),
  ),
  'urn:mace:surffederatie_nl:attribute-def:nlEduPersonStudyBranch' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Opleiding',
      'en_US' => 'Study Branch',
    ),
    'Description' =>
    array (
      'nl_NL' => 'opleiding; numerieke string die de CROHOcode bevat. leeg als het een niet reguliere opleiding betreft',
      'en_US' => 'study branch; numeric string which contains the CROHOcode. can be empty if the branch is unknown',
    ),
  ),
  'urn:mace:surffederatie_nl:attribute-def:nlStudielinkNummer' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'Studielinknummer',
      'en_US' => 'Studielinknummer',
    ),
    'Description' =>
    array (
      'nl_NL' => 'studielinknummer van student zoals geregistreerd bij www.studielink.nl',
      'en_US' => 'studielinknummer of the student as registered at www.studielink.nl',
    ),
  ),
  'urn:mace:surffederatie_nl:attribute-def:nlDigitalAuthorIdentifier' =>
  array (
    'Name' =>
    array (
      'nl_NL' => 'DAI',
      'en_US' => 'DAI',
    ),
    'Description' =>
    array (
      'nl_NL' => 'Digital Author Identifier (DAI) zoals beschreven op: http://www.surffoundation.nl/smartsite.dws?ch=eng&id=13480',
      'en_US' => 'Digital Author Identifier (DAI) as described at: http://www.surffoundation.nl/smartsite.dws?ch=eng&id=13480',
    ),
  ),
);