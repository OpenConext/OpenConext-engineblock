<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * PHP Array with URN identifiers for attributes with corresponding names and descriptions in IETF language tag format.
 *
 * @var array $attributes
 */
$attributes = array(
    'urn:mace:dir:attribute-def:uid' =>
    array(
        'Name' =>
        array(
            'nl' => 'UID',
            'en' => 'UID',
        ),
        'Description' =>
        array(
            'nl' => 'jouw unieke gebruikersnaam binnen jouw instelling',
            'en' => 'your unique username within your organization',
        ),
    ),
    'urn:mace:dir:attribute-def:sn' =>
    array(
        'Name' =>
        array(
            'nl' => 'Achternaam',
            'en' => 'Surname',
        ),
        'Description' =>
        array(
            'nl' => 'jouw achternaam',
            'en' => 'your surname',
        ),
    ),
    'urn:mace:dir:attribute-def:givenName' =>
    array(
        'Name' =>
        array(
            'nl' => 'Voornaam',
            'en' => 'Name',
        ),
        'Description' =>
        array(
            'nl' => 'voornaam/roepnaam',
            'en' => 'your name',
        ),
    ),
    'urn:mace:dir:attribute-def:cn' =>
    array(
        'Name' =>
        array(
            'nl' => 'Volledige persoonsnaam',
            'en' => 'Full Name',
        ),
        'Description' =>
        array(
            'nl' => 'volledige persoonsnaam',
            'en' => 'your full name',
        ),
    ),
    'urn:mace:dir:attribute-def:displayName' =>
    array(
        'Name' =>
        array(
            'nl' => 'Weergavenaam',
            'en' => 'Display Name',
        ),
        'Description' =>
        array(
            'nl' => 'weergave naam zoals getoond in applicaties',
            'en' => 'display name as shown in applications',
        ),
    ),
    'urn:mace:dir:attribute-def:mail' =>
    array(
        'Name' =>
        array(
            'nl' => 'E-mailadres',
            'en' => 'E-mailaddress',
        ),
        'Description' =>
        array(
            'nl' => 'jouw e-mailadres zoals bekend binnen jouw instelling',
            'en' => 'your e-mailaddress as known within your organization',
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonAffiliation' =>
    array(
        'Name' =>
        array(
            'nl' => 'Relatie',
            'en' => 'Relation',
        ),
        'Description' =>
        array(
            'nl' => 'geeft de relatie aan tussen jou en jouw instelling',
            'en' => 'relation between your and your organization',
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonEntitlement' =>
    array(
        'Name' =>
        array(
            'nl' => 'Rechtaanduiding',
            'en' => 'Entitlement',
        ),
        'Description' =>
        array(
            'nl' => 'rechtaanduiding; URI (URL of URN) dat een recht op iets aangeeft; wordt bepaald door een contract tussen dienstaanbieder en instelling',
            'en' => 'entitlement which decides upon your authorization within the application',
        ),
    ),
    'urn:mace:dir:attribute-def:eduPersonPrincipalName' =>
    array(
        'Name' =>
        array(
            'nl' => 'Net-ID',
            'en' => 'Net-ID',
        ),
        'Description' =>
        array(
            'nl' => 'jouw unieke gebruikersnaam binnen jouw instelling aangevuld met "@instellingsnaam.nl"',
            'en' => 'your unique username within your organization augmented with "@organizationname.nl"',
        ),
    ),
    'urn:mace:dir:attribute-def:preferredLanguage' =>
    array(
        'Name' =>
        array(
            'nl' => 'Voorkeurstaal',
            'en' => 'Preferred Language',
        ),
        'Description' =>
        array(
            'nl' => 'een tweeletterige afkorting van de voorkeurstaal volgens de ISO 639 taalafkortings codetabel; geen subcodes',
            'en' => 'a two letter abbreviation according to ISO 639; no subcodes',
        ),
    ),
    'urn:mace:terena.org:attribute-def:schacHomeOrganization' =>
    array(
        'Name' =>
        array(
            'nl' => 'Organisatie',
            'en' => 'Organization',
        ),
        'Description' =>
        array(
            'nl' => 'aanduiding voor de organisatie van een persoon gebruikmakend van de domeinnaam van de organisatie; syntax conform RFC 1035',
            'en' => 'name for the organization, making use of the domain name of the  organization conform RFC 1035',
        ),
    ),
    'urn:mace:terena.org:attribute-def:schacHomeOrganizationType' =>
    array(
        'Name' =>
        array(
            'nl' => 'Type Organisatie',
            'en' => 'Type of Organization',
        ),
        'Description' =>
        array(
            'nl' => 'aanduiding voor het type organisatie waartoe een persoon behoort, gebruikmakend van de waarden zoals geregisteerd door Terena op: http://www.terena.org/registry/terena.org/schac/homeOrganizationType',
            'en' => 'type of organization to which the user belongs',
        ),
    ),
    'urn:mace:surffederatie.nl:attribute-def:nlEduPersonHomeOrganization' =>
    array(
        'Name' =>
        array(
            'nl' => 'Weergavenaam van de Instelling',
            'en' => 'Display name of Organization',
        ),
        'Description' =>
        array(
            'nl' => 'weergavenaam van de instelling',
            'en' => 'display name of the organization',
        ),
    ),
    'urn:mace:surffederatie.nl:attribute-def:nlEduPersonOrgUnit' =>
    array(
        'Name' =>
        array(
            'nl' => 'Afdelingsnaam',
            'en' => 'Unitname',
        ),
        'Description' =>
        array(
            'nl' => ' naam van de afdeling',
            'en' => 'unit name',
        ),
    ),
    'urn:mace:surffederatie.nl:attribute-def:nlEduPersonStudyBranch' =>
    array(
        'Name' =>
        array(
            'nl' => 'Opleiding',
            'en' => 'Study Branch',
        ),
        'Description' =>
        array(
            'nl' => 'opleiding; numerieke string die de CROHOcode bevat. leeg als het een niet reguliere opleiding betreft',
            'en' => 'study branch; numeric string which contains the CROHOcode. can be empty if the branch is unknown',
        ),
    ),
    'urn:mace:surffederatie.nl:attribute-def:nlStudielinkNummer' =>
    array(
        'Name' =>
        array(
            'nl' => 'Studielinknummer',
            'en' => 'Studielinknummer',
        ),
        'Description' =>
        array(
            'nl' => 'studielinknummer van student zoals geregistreerd bij www.studielink.nl',
            'en' => 'studielinknummer of the student as registered at www.studielink.nl',
        ),
    ),
    'urn:mace:surffederatie.nl:attribute-def:nlDigitalAuthorIdentifier' =>
    array(
        'Name' =>
        array(
            'nl' => 'DAI',
            'en' => 'DAI',
        ),
        'Description' =>
        array(
            'nl' => 'Digital Author Identifier (DAI) zoals beschreven op: http://www.surffoundation.nl/smartsite.dws?ch=eng&id=13480',
            'en' => 'Digital Author Identifier (DAI) as described at: http://www.surffoundation.nl/smartsite.dws?ch=eng&id=13480',
        ),
    ),
    'urn:mace:surffederatie_nl:attribute-def:nlEduPersonHomeOrganization' =>
    array(
        'Name' =>
        array(
            'nl' => 'Weergavenaam van de Instelling',
            'en' => 'Display name of Organization',
        ),
        'Description' =>
        array(
            'nl' => 'weergavenaam van de instelling',
            'en' => 'display name of the organization',
        ),
    ),
    'urn:mace:surffederatie_nl:attribute-def:nlEduPersonOrgUnit' =>
    array(
        'Name' =>
        array(
            'nl' => 'Afdelingsnaam',
            'en' => 'Unitname',
        ),
        'Description' =>
        array(
            'nl' => ' naam van de afdeling',
            'en' => 'unit name',
        ),
    ),
    'urn:mace:surffederatie_nl:attribute-def:nlEduPersonStudyBranch' =>
    array(
        'Name' =>
        array(
            'nl' => 'Opleiding',
            'en' => 'Study Branch',
        ),
        'Description' =>
        array(
            'nl' => 'opleiding; numerieke string die de CROHOcode bevat. leeg als het een niet reguliere opleiding betreft',
            'en' => 'study branch; numeric string which contains the CROHOcode. can be empty if the branch is unknown',
        ),
    ),
    'urn:mace:surffederatie_nl:attribute-def:nlStudielinkNummer' =>
    array(
        'Name' =>
        array(
            'nl' => 'Studielinknummer',
            'en' => 'Studielinknummer',
        ),
        'Description' =>
        array(
            'nl' => 'studielinknummer van student zoals geregistreerd bij www.studielink.nl',
            'en' => 'studielinknummer of the student as registered at www.studielink.nl',
        ),
    ),
    'urn:mace:surffederatie_nl:attribute-def:nlDigitalAuthorIdentifier' =>
    array(
        'Name' =>
        array(
            'nl' => 'DAI',
            'en' => 'DAI',
        ),
        'Description' =>
        array(
            'nl' => 'Digital Author Identifier (DAI) zoals beschreven op: http://www.surffoundation.nl/smartsite.dws?ch=eng&id=13480',
            'en' => 'Digital Author Identifier (DAI) as described at: http://www.surffoundation.nl/smartsite.dws?ch=eng&id=13480',
        ),
    ),
    'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1' => array( // surfPersonAffiliation
        'Name' =>
        array(
            'nl' => 'Accountstatus',
            'en' => 'Accountstatus',
        ),
        'Description' =>
        array(
            'nl' => 'Status van deze account in de SURFfederatie',
            'en' => 'Status of this account in the SURFfederation',
        ),
    ),
    'urn:oid:1.3.6.1.4.1.5923.1.1.1.1' => array( // surfPersonAffiliation
        'Name' =>
        array(
            'nl' => 'Accountstatus',
            'en' => 'Accountstatus',
        ),
        'Description' =>
        array(
            'nl' => 'Status van deze account in de SURFfederatie',
            'en' => 'Status of this account in the SURFfederation',
        ),
    ),
    'nameid' => array( // federative identifier
        'Name' =>
        array(
            'nl' => 'Identifier',
            'en' => 'Identifier',
        ),
        'Description' =>
        array(
            'nl' => 'Status van deze account in de SURFfederatie',
            'en' => 'Status of this account in the SURFfederation',
        ),
    ),
    'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2' => array( // VO Name attribute
        'Name' =>
        array(
            'nl' => 'Naam Virtuele Organisatie',
            'en' => 'Virtual Organisation Name',
        ),
        'Description' =>
        array(
            'nl' => 'De naam van de Virtuele Organisatie waarvoor je bent ingelogd.',
            'en' => 'The name of the Virtual Urganisation for which you have authenticated',
        ),
    ),
    'urn:oid:1.3.6.1.4.1.1076.20.40.40.1' => array(
        'Name' => array(
            'nl' => 'Identifier',
            'en' => 'Identifier',
        ),
        'Description' => array(
            'nl' => 'Status van deze account in de SURFfederatie',
            'en' => 'Status of this account in the SURFfederation',
        ),
    ),
    'urn:oid:1.3.6.1.4.1.5923.1.1.1.10' => array(
        'Name' => array(
            'nl' => 'Identifier',
            'en' => 'Identifier',
        ),
        'Description' => array(
            'nl' => 'Status van deze account in de SURFfederatie',
            'en' => 'Status of this account in the SURFfederation',
        ),
    ),
    'urn:nl.surfconext.licenseInfo' => array(
        'Name' => array(
            'nl' => 'Licentieinformatie',
            'en' => 'License information'
        ),
        'Description' => array(
            'nl' => 'Licentie informatie voor de huidige dienst',
            'en' => 'License information for the current service',
        ),
    ),
    'urn:oid:1.3.6.1.4.1.5923.1.5.1.1' => array(
        'Name' => array(
            'nl' => 'Lidmaatschap',
            'en' => 'Membership',
        ),
        'Description' => array(
            'nl' => 'Lidmaatschap van virtuele organisaties en de SURFfederatie',
            'en' => 'Membership of Virtual Organizations and the SURFfederation.',
        ),
    ),
);