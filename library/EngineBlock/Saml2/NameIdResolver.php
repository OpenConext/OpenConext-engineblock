<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Doctrine\DBAL\Statement;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use SAML2\AuthnRequest;
use SAML2\Constants;
use SAML2\XML\saml\NameID;

class EngineBlock_Saml2_NameIdResolver
{
    const PERSISTENT_NAMEID_SALT = 'COIN:';

    /**
     * Note the significant ordering, from least privacy sensitive to most privacy sensitive.
     * See also:
     * @url https://jira.surfconext.nl/jira/browse/BACKLOG-673
     *
     * @var array
     */
    private $SUPPORTED_NAMEID_FORMATS = array(
        Constants::NAMEID_PERSISTENT,
        Constants::NAMEID_TRANSIENT,
        Constants::NAMEID_UNSPECIFIED,
    );

    /**
     * NameId cache.
     *
     * Only persistent name id's are cached for each request. Transient name id's are stored
     * in session and the unspecified id is already known to us.
     *
     * The values are indexed by a combination of name id and sp-entity id.
     *
     * Example:
     * 'urn:collab:person:example.com:admin:https://my-sp-entity-id/saml2/metadata'
     *
     * @var string[]
     */
    private $cache = array();

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @param ServiceProvider $destinationMetadata
     * @param $collabPersonId
     * @return NameID
     */
    public function resolve(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        EngineBlock_Saml2_ResponseAnnotationDecorator $response,
        ServiceProvider $destinationMetadata,
        $collabPersonId
    ) {
        $customNameId = $response->getCustomNameId();
        if ($customNameId !== null) {
            return $customNameId;
        }

        $nameIdFormat = $this->_getNameIdFormat($request, $destinationMetadata);

        $requireUnspecified = ($nameIdFormat === Constants::NAMEID_UNSPECIFIED);
        if ($requireUnspecified) {
            $this->logger->info('Resolving an unspecified nameId');
            $nameId = new NameID();
            $nameId->setFormat($nameIdFormat);
            $nameId->setValue($response->getIntendedNameId());
            return $nameId;
        }

        $requireTransient = ($nameIdFormat === Constants::NAMEID_TRANSIENT);
        if ($requireTransient) {
            $this->logger->info('Resolving a transient nameId');
            $nameId = new NameID();
            $nameId->setFormat($nameIdFormat);
            $nameId->setValue($this->_getTransientNameId($destinationMetadata->entityId, $response->getOriginalIssuer()));
            return $nameId;
        }

        $this->logger->info('Resolving a persistent nameId');
        $nameId = new NameID();
        $nameId->setFormat($nameIdFormat);
        $nameId->setValue($this->_getPersistentNameId($collabPersonId, $destinationMetadata->entityId));
        return $nameId;
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
        $nameIdFromSession = $this->_getTransientNameIdFromSession($spId, $idpId);
        if ($nameIdFromSession) {
            return $nameIdFromSession;
        }

        $nameId = $this->_generateTransientNameId();

        $this->_storeTransientNameIdToSession($nameId, $spId, $idpId);
        return $nameId;
    }

    protected function _generateTransientNameId()
    {
        return sha1((string)mt_rand(0, mt_getrandmax()));
    }

    protected function _getTransientNameIdFromSession($spId, $idpId)
    {
        return isset($_SESSION[$spId][$idpId]) ? $_SESSION[$spId][$idpId] : false;
    }

    protected function _storeTransientNameIdToSession($nameId, $spId, $idpId)
    {
        // store to session
        $_SESSION[$spId][$idpId] = $nameId;
    }

    protected function _getNameIdFormat(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        ServiceProvider $spEntityMetadata
    ) {
        // If a NameIDFormat was explicitly set in the ServiceRegistry, use that...
        if ($spEntityMetadata->nameIdFormat) {
            return $spEntityMetadata->nameIdFormat;
        }

        // If the SP requests a specific NameIDFormat in their AuthnRequest
        /** @var AuthnRequest $request */
        $nameIdPolicy = $request->getNameIdPolicy();
        if (!empty($nameIdPolicy['Format'])) {
            $mayUseRequestedNameIdFormat = true;
            $requestedNameIdFormat = $nameIdPolicy['Format'];

            // Do we support the NameID Format that the SP requests?
            if (!in_array($requestedNameIdFormat, $this->SUPPORTED_NAMEID_FORMATS)) {
                EngineBlock_ApplicationSingleton::getLog()->notice(
                    "Whoa, SP '{$spEntityMetadata->entityId}' requested '{$requestedNameIdFormat}' " .
                    "however we don't support that format, opting to try something else it supports " .
                    "instead of sending an error. SP might not be happy with this violation of the spec " .
                    "but it's probably a lot happier with a valid Response than an Error Response"
                );
                $mayUseRequestedNameIdFormat = false;
            }

            // Is this SP restricted to specific NameIDFormats?
            if (!empty($spEntityMetadata->supportedNameIdFormats)) {
                if (!in_array($requestedNameIdFormat, $spEntityMetadata->supportedNameIdFormats)) {
                    EngineBlock_ApplicationSingleton::getLog()->notice(
                        "Whoa, SP '{$spEntityMetadata->entityId}' requested '{$requestedNameIdFormat}' " .
                        "opting to try something else it supports " .
                        "instead of sending an error. SP might not be happy with this violation of the spec " .
                        "but it's probably a lot happier with a valid Response than an Error Response"
                    );

                    $mayUseRequestedNameIdFormat = false;
                }
            }

            if ($mayUseRequestedNameIdFormat) {
                return $requestedNameIdFormat;
            }
        }

        // So neither a NameIDFormat is explicitly set in the metadata OR a (valid) NameIDPolicy is set in the AuthnRequest
        // so we check what the SP supports (or what Manage claims that it supports) and
        // return the least privacy sensitive one.
        if (!empty($spEntityMetadata->supportedNameIdFormats)) {
            foreach ($this->SUPPORTED_NAMEID_FORMATS as $supportedNameIdFormat) {
                if (in_array($supportedNameIdFormat, $spEntityMetadata->supportedNameIdFormats)) {
                    return $supportedNameIdFormat;
                }
            }
        }

        throw new EngineBlock_Exception(
            sprintf(
                'Whoa, SP "%s" has no NameIDFormat set, did send a (valid) NameIDPolicy and has no supported NameIDFormats set... I give up...',
                $spEntityMetadata->entityId
            ),
            EngineBlock_Exception::CODE_NOTICE
        );
    }

    /**
     * Retrieve the Persistent name id for a user for a given service provider
     *
     * The id is stored to cache as EB will retrieve this data multiple times per request. This
     * prevents unnecessary database traffic.
     *
     * @param $originalCollabPersonId
     * @param $spEntityId
     * @return bool|string
     * @throws EngineBlock_Exception
     */
    protected function _getPersistentNameId($originalCollabPersonId, $spEntityId)
    {
        // Attempt to load the result from cache
        $cacheIdentifier = $originalCollabPersonId . ':' .$spEntityId;
        if (isset($this->cache[$cacheIdentifier])) {
            return $this->cache[$cacheIdentifier];
        }

        // Generate the persistent id
        $serviceProviderUuid = $this->_getServiceProviderUuid($spEntityId);
        $userUuid            = $this->_getUserUuid($originalCollabPersonId);
        $persistentId = $this->_fetchPersistentId($serviceProviderUuid, $userUuid);

        if (!$persistentId) {
            $persistentId = $this->_generatePersistentId($serviceProviderUuid, $userUuid);
            $this->_storePersistentId($persistentId, $serviceProviderUuid, $userUuid);
        }

        // Store the persistent name id in cache for the duration of the request
        $this->cache[$cacheIdentifier] = $persistentId;

        return $persistentId;
    }

    protected function _getServiceProviderUuid($spEntityId)
    {
        $uuid = $this->_fetchServiceProviderUuid($spEntityId);

        if ($uuid) {
            return $uuid;
        }

        $uuid = (string) Uuid::uuid4();
        $this->_storeServiceProviderUuid($spEntityId, $uuid);

        return $uuid;
    }

    protected function _fetchServiceProviderUuid($spEntityId)
    {
        $statement = $this->_getDb()->prepare(
            'SELECT uuid FROM service_provider_uuid WHERE service_provider_entity_id=?'
        );
        assert($statement instanceof Statement);
        $statement->bindValue(1, $spEntityId);
        $resultSet = $statement->executeQuery();


        if ($resultSet->rowCount() > 1) {
            throw new EngineBlock_Exception(sprintf('Multiple SP UUIDs found? For SP: "%s"', $spEntityId));
        }

        if($resultSet->rowCount() === 0){
            return false;
        }

        $result = $resultSet->fetchAllAssociative();

        return isset($result[0]['uuid']) ? $result[0]['uuid'] : false;
    }

    protected function _storeServiceProviderUuid($spEntityId, $uuid)
    {
        $statement = $this->_getDb()->prepare(
            'INSERT INTO service_provider_uuid (uuid, service_provider_entity_id) VALUES (?,?)'
        );

        $statement->bindValue(1, $uuid);
        $statement->bindValue(2, $spEntityId);

        $statement->executeStatement();
    }

    protected function _fetchPersistentId($serviceProviderUuid, $userUuid)
    {
        $statement = $this->_getDb()->prepare(
            "SELECT persistent_id FROM saml_persistent_id WHERE service_provider_uuid = ? AND user_uuid = ?"
        );

        $statement->bindValue(1, $serviceProviderUuid);
        $statement->bindValue(2, $userUuid);

        $resultSet = $statement->executeQuery();
        if ($resultSet->rowCount() > 1) {
            throw new EngineBlock_Exception(
                sprintf(
                    'Multiple persistent IDs found? For: SP UUID: "%s" and user UUID: "%s"',
                    $serviceProviderUuid,
                    $userUuid
                )
            );
        }

        $result = $resultSet->fetchAllAssociative();

        return isset($result[0]['persistent_id']) ? $result[0]['persistent_id'] : false;
    }

    protected function _generatePersistentId($serviceProviderUuid, $userUuid)
    {
        return sha1(self::PERSISTENT_NAMEID_SALT . $userUuid . $serviceProviderUuid);
    }

    protected function _storePersistentId($persistentId, $serviceProviderUuid, $userUuid)
    {
        $statement = $this->_getDb()->prepare(
            "INSERT INTO saml_persistent_id (persistent_id, service_provider_uuid, user_uuid) VALUES (?,?,?)"
        );

        $statement->bindValue(1, $persistentId);
        $statement->bindValue(2, $serviceProviderUuid);
        $statement->bindValue(3, $userUuid);

        $statement->executeStatement();
    }

    protected function _getDb(): Doctrine\DBAL\Connection
    {
        static $s_db;
        if ($s_db) {
            return $s_db;
        }

        $factory = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getDatabaseConnectionFactory();
        $s_db = $factory->create();

        return $s_db;
    }

    protected function _getUserUuid($collabPersonId)
    {
        $userDirectory = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getUserDirectory();
        $user = $userDirectory->findUserBy($collabPersonId);

        if (!$user) {
            throw new EngineBlock_Exception(sprintf('No users found for collabPersonId: "%s"', $collabPersonId));
        }

        return $user->getCollabPersonUuid()->getUuid();
    }
}
