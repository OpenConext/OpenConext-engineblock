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
 * @copyright Copyright ¬© 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class SurfConext_User
{
    const URN_COLLAB_PERSON_NAMESPACE = 'urn:collab:person';

    // Top
//    protected $_objectClass;

    // Person
    # MUST
    protected $_sn;
    protected $_cn;
    # MAY
//    protected $_userPassword;
//    protected $_telephoneNumber;
//    protected $_seeAlso;
//    protected $_description;

    // OrgPerson
//    protected $_title;
//    protected $_x121Address;
//    protected $_registeredAddress;
//    protected $_destinationIndicator;
//    protected $_preferredDeliveryMethod;
//    protected $_telexNumber;
//    protected $_teletexTerminalIdentifier;
//    protected $_internationaliSDNNumber;
//    protected $_facsimileTelephoneNumber;
//    protected $_street;
//    protected $_postOfficeBox;
//    protected $_postalCode;
//    protected $_postalAddress;
//    protected $_physicalDeliveryOfficeName;
//    protected $_ou;
//    protected $_st;
//    protected $_l;

    // inetOrgPerson    
//    protected $_audio;
//    protected $_businessCategory;
//    protected $_carLicense;
//    protected $_departmentNumber;
    protected $_displayName;
//    protected $_employeeNumber;
//    protected $_employeeType;
    protected $_givenName;
//    protected $_homePhone;
//    protected $_homePostalAddress;
//    protected $_initials;
//    protected $_jpegPhoto;
//    protected $_labeledURI;
    protected $_mail;
//    protected $_manager;
//    protected $_mobile;
    protected $_o;
//    protected $_pager;
//    protected $_photo;
//    protected $_roomNumber;
//    protected $_secretary;
    protected $_uid;
//    protected $_userCertificate;
//    protected $_x500uniqueIdentifier;
//    protected $_preferredLanguage;
//    protected $_userSMIMECertificate;
//    protected $_userPKCS12;

    // eduPerson spec
    # MAY
    protected $_eduPersonAffiliation;
    protected $_eduPersonNickname;
    protected $_eduPersonOrgDN;
    protected $_eduPersonOrgUnitDN;
    protected $_eduPersonPrimaryAffiliation;
    protected $_eduPersonPrincipalName;
    protected $_eduPersonEntitlement;
    protected $_eduPersonPrimaryOrgUnitDN;
    protected $_eduPersonScopedAffiliation;

    // nlEduPerson spec
    # MAY
    protected $_nlEduPersonOrgUnit;
    protected $_nlEduPersonStudyBranch;
    protected $_nlStudielinkNummer;

    // collabPersonSpec
    # MUST
    protected $_collabPersonId;
    protected $_collabPersonUuid;
    # MAY
    protected $_collabPersonHash;
    protected $_collabPersonIsGuest = TRUE;

    protected $_collabPersonFirstWarningSent = FALSE;
    protected $_collabPersonSecondWarningSent = FALSE;
      # Metadata
    protected $_collabPersonRegistered;
    protected $_collabPersonLastUpdated;
    protected $_collabPersonLastAccessed;

    //

    public function __construct()
    {
    }

    public function setCommonName($cn)
    {
        $this->_cn = $cn;
        return $this;
    }

    public function getCommonName()
    {
        return $this->_cn;
    }

    public function setDisplayName($displayName)
    {
        $this->_displayName = $displayName;
        return $this;
    }

    public function getDisplayName()
    {
        return $this->_displayName;
    }

    public function setOrganization($o)
    {
        $this->_o = $o;
        return $this;
    }

    public function getOrganization()
    {
        return $this->_o;
    }

    public function setSurname($sn)
    {
        $this->_sn = $sn;
        return $this;
    }

    public function getSurname()
    {
        return $this->_sn;
    }

    public function setUid($uid)
    {
        $this->_uid = $uid;
        return $this;
    }

    public function getUid()
    {
        return $this->_uid;
    }

    public function setGivenName($givenName)
    {
        $this->_givenName = $givenName;
        return $this;
    }

    public function getGivenName()
    {
        return $this->_givenName;
    }

    public function setMail($mail)
    {
        $this->_mail = $mail;
    }

    public function getMail()
    {
        return $this->_mail;
    }

    # CollabPerson Attributes

    public function setCollabPersonFirstWarningSent($collabPersonFirstWarningSent)
    {
        $this->_collabPersonFirstWarningSent = $collabPersonFirstWarningSent;
        return $this;
    }

    public function getCollabPersonFirstWarningSent()
    {
        return $this->_collabPersonFirstWarningSent;
    }

    public function setCollabPersonHash($collabPersonHash)
    {
        $this->_collabPersonHash = $collabPersonHash;
        return $this;
    }

    public function getCollabPersonId()
    {
        if (!isset($this->_uid)) {
            throw new Exception("Unable to get collabPersonId for user, no UID set!");
        }
        if (!isset($this->_o)) {
            throw new Exception("Unable to get collabPersonId for user, no Organization set!");
        }

        $uid = str_replace('@', '_', $this->_uid);
        return self::URN_COLLAB_PERSON_NAMESPACE . ':' . $this->_o . ':' . $uid;
    }

    public function setCollabPersonIsGuest($collabPersonIsGuest)
    {
        $this->_collabPersonIsGuest = $collabPersonIsGuest;
        return $this;
    }

    public function getCollabPersonIsGuest()
    {
        return $this->_collabPersonIsGuest;
    }

    public function setCollabPersonLastAccessed($collabPersonLastAccessed)
    {
        $this->_collabPersonLastAccessed = $collabPersonLastAccessed;
        return $this;
    }

    public function getCollabPersonLastAccessed()
    {
        return $this->_collabPersonLastAccessed;
    }

    public function setCollabPersonLastUpdated($collabPersonLastUpdated)
    {
        $this->_collabPersonLastUpdated = $collabPersonLastUpdated;
        return $this;
    }

    public function getCollabPersonLastUpdated()
    {
        return $this->_collabPersonLastUpdated;
    }

    public function setCollabPersonRegistered($collabPersonRegistered)
    {
        $this->_collabPersonRegistered = $collabPersonRegistered;
        return $this;
    }

    public function getCollabPersonRegistered()
    {
        return $this->_collabPersonRegistered;
    }

    public function setCollabPersonSecondWarningSent($collabPersonSecondWarningSent)
    {
        $this->_collabPersonSecondWarningSent = $collabPersonSecondWarningSent;
        return $this;
    }

    public function getCollabPersonSecondWarningSent()
    {
        return $this->_collabPersonSecondWarningSent;
    }

    public function setCollabPersonUuid($collabPersonUuid)
    {
        $this->_collabPersonUuid = $collabPersonUuid;
        return $this;
    }

    public function getCollabPersonUuid()
    {
        return $this->_collabPersonUuid;
    }

    # EduPerson Attributes

    public function setEduPersonAffiliation($eduPersonAffiliation)
    {
        $this->_eduPersonAffiliation = $eduPersonAffiliation;
        return $this;
    }

    public function getEduPersonAffiliation()
    {
        return $this->_eduPersonAffiliation;
    }

    public function setEduPersonEntitlement($eduPersonEntitlement)
    {
        $this->_eduPersonEntitlement = $eduPersonEntitlement;
        return $this;
    }

    public function getEduPersonEntitlement()
    {
        return $this->_eduPersonEntitlement;
    }

    public function setEduPersonNickname($eduPersonNickname)
    {
        $this->_eduPersonNickname = $eduPersonNickname;
        return $this;
    }

    public function getEduPersonNickname()
    {
        return $this->_eduPersonNickname;
    }

    public function setEduPersonOrgDN($eduPersonOrgDN)
    {
        $this->_eduPersonOrgDN = $eduPersonOrgDN;
        return $this;
    }

    public function getEduPersonOrgDN()
    {
        return $this->_eduPersonOrgDN;
    }

    public function setEduPersonOrgUnitDN($eduPersonOrgUnitDN)
    {
        $this->_eduPersonOrgUnitDN = $eduPersonOrgUnitDN;
        return $this;
    }

    public function getEduPersonOrgUnitDN()
    {
        return $this->_eduPersonOrgUnitDN;
    }

    public function setEduPersonPrimaryAffiliation($eduPersonPrimaryAffiliation)
    {
        $this->_eduPersonPrimaryAffiliation = $eduPersonPrimaryAffiliation;
        return $this;
    }

    public function getEduPersonPrimaryAffiliation()
    {
        return $this->_eduPersonPrimaryAffiliation;
    }

    public function setEduPersonPrimaryOrgUnitDN($eduPersonPrimaryOrgUnitDN)
    {
        $this->_eduPersonPrimaryOrgUnitDN = $eduPersonPrimaryOrgUnitDN;
        return $this;
    }

    public function getEduPersonPrimaryOrgUnitDN()
    {
        return $this->_eduPersonPrimaryOrgUnitDN;
    }

    public function setEduPersonPrincipalName($eduPersonPrincipalName)
    {
        $this->_eduPersonPrincipalName = $eduPersonPrincipalName;
        return $this;
    }

    public function getEduPersonPrincipalName()
    {
        return $this->_eduPersonPrincipalName;
    }

    public function setEduPersonScopedAffiliation($eduPersonScopedAffiliation)
    {
        $this->_eduPersonScopedAffiliation = $eduPersonScopedAffiliation;
        return $this;
    }

    public function getEduPersonScopedAffiliation()
    {
        return $this->_eduPersonScopedAffiliation;
    }

    # nlEduPerson attributes

    public function setNlEduPersonOrgUnit($nlEduPersonOrgUnit)
    {
        $this->_nlEduPersonOrgUnit = $nlEduPersonOrgUnit;
        return $this;
    }

    public function getNlEduPersonOrgUnit()
    {
        return $this->_nlEduPersonOrgUnit;
    }

    public function setNlEduPersonStudyBranch($nlEduPersonStudyBranch)
    {
        $this->_nlEduPersonStudyBranch = $nlEduPersonStudyBranch;
        return $this;
    }

    public function getNlEduPersonStudyBranch()
    {
        return $this->_nlEduPersonStudyBranch;
    }

    public function setNlStudielinkNummer($nlStudielinkNummer)
    {
        $this->_nlStudielinkNummer = $nlStudielinkNummer;
        return $this;
    }

    public function getNlStudielinkNummer()
    {
        return $this->_nlStudielinkNummer;
    }

    public function getCollabPersonHash(SurfConext_User $user)
    {
        return md5($user->__toString());
    }
//
//    public function __get($property)
//    {
//        $methodName = 'get' . ucfirst($property);
//        if (!method_exists($this, $methodName)) {
//            throw new RuntimeException("User model does not support access of $property");
//        }
//        return $this->$methodName;
//    }
//
//    public function __set($property, $value)
//    {
//        $methodName = 'set' . ucfirst($property);
//        if (!method_exists($this, $methodName)) {
//            throw new RuntimeException("User model does not support access of $property");
//        }
//        return $this->$methodName($value);
//    }
//
//    public function __toString()
//    {
//        return serialize($this);
//    }

    public function toArray()
    {
        $objectVars = get_object_vars($this);

        // Remove leading underscores.
        $data = array();
        foreach ($objectVars as $key => $val) {
            if (substr($key, 0, 1) === '_') {
                $key = substr($key, 1);
            }
            $data[$key] = $val;
        }

        return $data;
    }
}