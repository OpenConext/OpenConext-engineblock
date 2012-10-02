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

class EngineBlock_Corto_Filter_Command_SetPersistentId extends EngineBlock_Corto_Filter_Command_Abstract
{
    const PERSISTENT_NAMEID_SALT = 'COIN:';

    const SAML2_NAME_ID_FORMAT_UNSPECIFIED  = 'urn:oasis:names:tc:SAML:2.0:nameid-format:unspecified';
    const SAML2_NAME_ID_FORMAT_TRANSIENT    = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    const SAML2_NAME_ID_FORMAT_PERSISTENT   = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';

    private $SUPPORTED_NAMEID_FORMATS = array(
        self::SAML2_NAME_ID_FORMAT_UNSPECIFIED,
        self::SAML2_NAME_ID_FORMAT_TRANSIENT,
        self::SAML2_NAME_ID_FORMAT_PERSISTENT,
    );

    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        if (isset($this->_response['__']['CustomNameId'])) {
            $nameId = $this->_response['__']['CustomNameId'];
        }
        else {
            $nameIdFormat = $this->_getNameIdFormat($this->_request, $this->_spMetadata);

            if ($nameIdFormat === self::SAML2_NAME_ID_FORMAT_UNSPECIFIED) {
                $nameIdValue = $this->_response['__']['IntendedNameId'];
            } else if ($nameIdFormat === self::SAML2_NAME_ID_FORMAT_TRANSIENT) {
                $nameIdValue = $this->_getTransientNameId(
                    $this->_spMetadata['EntityId'], $this->_idpMetadata['EntityId']
                );
            } else {
                $nameIdValue = $this->_getPersistentNameId(
                    $this->_collabPersonId,
                    $this->_spMetadata['EntityId']
                );

            }
            $nameId = array(
                '_Format' => $nameIdFormat,
                '__v'     => $nameIdValue,
            );
        }

        // Adjust the NameID in the NEW response, set the collab:person uid
        $this->_response['saml:Assertion']['saml:Subject']['saml:NameID'] = $nameId;

        // Add the eduPersonTargetedId
        $this->_responseAttributes['urn:mace:dir:attribute-def:eduPersonTargetedID'] = array(
            array(
                "saml:NameID" => $nameId,
            )
        );
    }

    /**
     * Load transient Name ID from session or generate a new one
     *
     * @param string $spId
     * @param string $idpId
     * @return string
     */
    protected function _getTransientNameId($spId, $idpId)
    {
        if (!empty($_SESSION[$spId][$idpId])) {
            return $_SESSION[$spId][$idpId];
        }

        $nameId = sha1((string)mt_rand(0, mt_getrandmax()));

        // store to session
        $_SESSION[$spId][$idpId] = $nameId;

        return $nameId;
    }

    protected function _getNameIdFormat($request, $spEntityMetadata)
    {
        // Persistent is our default
        $defaultNameIdFormat = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';

        // If a NameIDFormat was explicitly set in the ServiceRegistry, use that...
        if (isset($spEntityMetadata['NameIDFormat'])) {
            return $spEntityMetadata['NameIDFormat'];
        }
        // If the SP requests a specific NameIDFormat in their AuthnRequest
        else if (isset($request['samlp:NameIDPolicy']['_Format'])) {
            $requestedNameIdFormat = $request['samlp:NameIDPolicy']['_Format'];
            if (in_array($requestedNameIdFormat, $this->SUPPORTED_NAMEID_FORMATS)) {
                return $request['samlp:NameIDPolicy']['_Format'];
            }
            else {
                EngineBlock_ApplicationSingleton::getLog()->warn(
                    "Whoa, SP '{$spEntityMetadata['EntityID']}' requested '{$requestedNameIdFormat}' " .
                        "however we don't support that format, opting to try '$defaultNameIdFormat' " .
                        "instead of sending an error. SP might not be happy with that..."
                );
                return $defaultNameIdFormat;
            }
        }
        return $defaultNameIdFormat;
    }

    protected function _getPersistentNameId($originalCollabPersonId, $spEntityId)
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        $db = $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);

        $serviceProviderUuid = $this->_getServiceProviderUuid($spEntityId, $db);
        $userUuid = $this->_getUserUuid($originalCollabPersonId);

        $statement = $db->prepare(
            "SELECT persistent_id FROM saml_persistent_id WHERE service_provider_uuid = ? AND user_uuid = ?"
        );
        $statement->execute(array($serviceProviderUuid, $userUuid));
        $rows = $statement->fetchAll();

        if (empty($rows)) {
            $persistentId = sha1(self::PERSISTENT_NAMEID_SALT . $userUuid . $serviceProviderUuid);
            $statement = $db->prepare(
                "INSERT INTO saml_persistent_id (persistent_id, service_provider_uuid, user_uuid) VALUES (?,?,?)"
            );
            $result = $statement->execute(array($persistentId, $serviceProviderUuid, $userUuid));
            if (!$result) {
                throw new EngineBlock_Exception(
                    'Unable to store new persistent id for SP UUID: ' . $serviceProviderUuid .
                        ' and user uuid: ' . $userUuid .
                        ' error info: ' . var_export($statement->errorInfo(), true),
                    $statement->errorCode()
                );
            }
            return $persistentId;
        }
        else if (count($rows) > 1) {
            throw new EngineBlock_Exception(
                'Multiple persistent IDs found? For: SPUUID: ' . $serviceProviderUuid . ' and user UUID: ' . $userUuid
            );
        }
        else {
            return $rows[0]['persistent_id'];
        }
    }

    protected function _getServiceProviderUuid($spEntityId, $db)
    {
        $statement = $db->prepare("SELECT uuid FROM service_provider_uuid WHERE service_provider_entity_id=?");
        $statement->execute(array($spEntityId));
        $result = $statement->fetchAll();

        if (empty($result)) {
            $uuid = (string)Surfnet_Zend_Uuid::generate();
            $statement = $db->prepare("INSERT INTO service_provider_uuid (uuid, service_provider_entity_id) VALUES (?,?)");
            $statement->execute(
                array(
                    $uuid,
                    $spEntityId,
                )
            );
        }
        else {
            $uuid = $result[0]['uuid'];
        }
        return $uuid;
    }

    protected function _getUserUuid($collabPersonId)
    {
        $userDirectory = new EngineBlock_UserDirectory(
            EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->ldap
        );
        $users = $userDirectory->findUsersByIdentifier($collabPersonId);
        if (count($users) > 1) {
            throw new EngineBlock_Exception('Multiple users found for collabPersonId: ' . $collabPersonId);
        }

        if (count($users) < 1) {
            throw new EngineBlock_Exception('No users found for collabPersonId: ' . $collabPersonId);
        }

        return $users[0]['collabpersonuuid'];
    }
}