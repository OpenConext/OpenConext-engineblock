<?php

return array(
    array(
        'cn' => array(
            0 => 'eduperson-200412',
        ),
        'dn' => 'cn=eduperson-200412,cn=schema,cn=config',
        'objectclass' =>
            array(
                0 => 'olcSchemaConfig',
            ),
        'olcattributetypes' =>
            array(
                0 => '{0}( 1.3.6.1.4.1.5923.1.1.1.1 NAME \'eduPersonAffiliation\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 )',
                1 => '{1}( 1.3.6.1.4.1.5923.1.1.1.2 NAME \'eduPersonNickname\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 )',
                2 => '{2}( 1.3.6.1.4.1.5923.1.1.1.3 NAME \'eduPersonOrgDN\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY distinguishedNameMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.12 SINGLE-VALUE )',
                3 => '{3}( 1.3.6.1.4.1.5923.1.1.1.4 NAME \'eduPersonOrgUnitDN\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY distinguishedNameMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.12 )',
                4 => '{4}( 1.3.6.1.4.1.5923.1.1.1.5 NAME \'eduPersonPrimaryAffiliation\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
                5 => '{5}( 1.3.6.1.4.1.5923.1.1.1.6 NAME \'eduPersonPrincipalName\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
                6 => '{6}( 1.3.6.1.4.1.5923.1.1.1.7 NAME \'eduPersonEntitlement\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY caseExactMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 )',
                7 => '{7}( 1.3.6.1.4.1.5923.1.1.1.8 NAME \'eduPersonPrimaryOrgUnitDN\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY distinguishedNameMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.12 SINGLE-VALUE )',
                8 => '{8}( 1.3.6.1.4.1.5923.1.1.1.9 NAME \'eduPersonScopedAffiliation\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
                9 => '{9}( 1.3.6.1.4.1.5923.1.1.1.10 NAME \'eduPersonStuff\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
                10 => '{10}( 1.3.6.1.4.1.5923.1.1.1.11 NAME \'eduPersonPromises\' DESC \'eduPerson per Internet2 and EDUCAUSE\' EQUALITY caseIgnoreMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
            ),
        'olcobjectclasses' =>
            array(
                0 => '{0}( 1.3.6.1.4.1.5923.1.1.2 NAME \'eduPerson\' AUXILIARY MAY ( eduPersonAffiliation $ eduPersonNickname $ eduPersonOrgDN $ eduPersonOrgUnitDN $ eduPersonPrimaryAffiliation $ eduPersonPrincipalName $ eduPersonEntitlement $ eduPersonPrimaryOrgUnitDN $ eduPersonScopedAffiliation ) )',
            ),
    ),
    array(
        'cn' =>
            array(
                0 => 'nleduperson',
            ),
        'dn' => 'cn=nleduperson,cn=schema,cn=config',
        'objectclass' =>
            array(
                0 => 'olcSchemaConfig',
            ),
        'olcattributetypes' =>
            array(
                0 => '{0}( 1.3.6.1.4.1.1076.20.40.20.10.1 NAME \'nlEduPersonOrgUnit\' DESC \'naam van de afdeling\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 )',
                1 => '{1}( 1.3.6.1.4.1.1076.20.40.20.10.2 NAME \'nlEduPersonStudyBranch\' DESC \'opleiding; numerieke string die de CROHOcode bevat; leeg als het een niet reguliere opleiding betreft\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 )',
                2 => '{2}( 1.3.6.1.4.1.1076.20.40.20.10.3 NAME \'nlStudielinkNummer\' DESC \'studielinknummer van student zoals geregistreerd bij www.studielink.nl\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
            ),
        'olcobjectclasses' =>
            array(
                0 => '{0}( 1.3.6.1.4.1.1076.20.40.20.10 NAME \'nlEduPerson\' DESC \'EduPerson - Nationaal gestandaardiseerde attributen\' SUP eduPerson AUXILIARY MAY ( nlEduPersonOrgUnit $ nlEduPersonStudyBranch $ nlStudielinkNummer ) )',
            ),
    ),
    array(
        'cn' =>
            array(
                0 => 'collab',
            ),
        'dn' => 'cn=collab,cn=schema,cn=config',
        'objectclass' =>
            array(
                0 => 'olcSchemaConfig',
            ),
        'olcattributetypes' =>
            array(
                0 => '{0}( 1.3.6.1.4.1.1076.20.40.40.1 NAME \'collabPersonId\' DESC \'Collab unique user identifier\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
                1 => '{1}( 1.3.6.1.4.1.1076.20.40.40.2 NAME \'collabPersonHash\' DESC \'attributes hash\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
                2 => '{2}( 1.3.6.1.4.1.1076.20.40.40.3 NAME \'collabPersonRegistered\' DESC \'Initial provisioned timestamp\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
                3 => '{3}( 1.3.6.1.4.1.1076.20.40.40.4 NAME \'collabPersonLastUpdated\' DESC \'Timestamp for last attributes update\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
                4 => '{4}( 1.3.6.1.4.1.1076.20.40.40.5 NAME \'collabPersonLastAccessed\' DESC \'Timestamp for last login\' EQUALITY caseIgnoreMatch SUBSTR caseIgnoreSubstringsMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.15 SINGLE-VALUE )',
                5 => '{5}( 1.3.6.1.4.1.1076.20.40.40.6 NAME \'collabPersonIsGuest\' DESC \'guest user\' EQUALITY booleanMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.7 SINGLE-VALUE )',
                6 => '{6}( 1.3.6.1.4.1.1076.20.40.40.7 NAME \'collabPersonFirstWarningSent\' DESC \'Is the first deprovisioning warning sent?\' EQUALITY booleanMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.7 SINGLE-VALUE ) ',
                7 => '{7}( 1.3.6.1.4.1.1076.20.40.40.8 NAME \'collabPersonSecondWarningSent\' DESC \'Is the second deprovisioning warning sent?\' EQUALITY booleanMatch SYNTAX 1.3.6.1.4.1.1466.115.121.1.7 SINGLE-VALUE ) ',
                8 => '{8}( 1.3.6.1.4.1.1076.20.40.40.9 NAME \'collabPersonUUID\' DESC \'UUID for person\' EQUALITY UUIDMatch ORDERING UUIDOrderingMatch SYNTAX 1.3.6.1.1.16.1 SINGLE-VALUE )',
            ),
        'olcobjectclasses' =>
            array(
                0 => '{0}( 1.3.6.1.4.1.1076.20.40.20.40 NAME \'collabPerson\' DESC \'collabPerson - SURFnet COIN attributen\' SUP eduPerson AUXILIARY MUST ( collabPersonId ) MAY ( collabPersonUUID $ collabPersonHash $ collabPersonRegistered $ collabPersonLastUpdated $ collabPersonLastAccessed $ collabPersonIsGuest $ collabPersonFirstWarningSent $ collabPersonSecondWarningSent ) )',
            ),
    ),
);