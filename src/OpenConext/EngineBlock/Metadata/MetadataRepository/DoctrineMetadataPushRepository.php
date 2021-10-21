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

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProviderEb5;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProviderEb5;
use RuntimeException;

/**
 * This class has added to temporary extra objects to push to both sso_provider_roles_eb5
 * and sso_provider_roles_eb6
 *
 * TODO: Remove this suppression after sso_provider_roles_eb5 has been phased out
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DoctrineMetadataPushRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ClassMetadata
     */
    private $spMetadata;

    /**
     * @var ClassMetadata
     */
    private $idpMetadata;

    /**
     * This field has been added to temporary push to both sso_provider_roles_eb5
     * and sso_provider_roles_eb6
     *
     * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
     *
     * @var ClassMetadata
     */
    private $spMetadataUpdated;

    /**
     * This field has been added to temporary push to both sso_provider_roles_eb5
     * and sso_provider_roles_eb6
     *
     * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
     *
     * @var ClassMetadata
     */
    private $idpMetadataUpdated;

    const ROLES_TABLE_NAME = 'sso_provider_roles_eb6';

    /**
     * This field has been added to temporary push to both sso_provider_roles_eb5
     * and sso_provider_roles_eb6
     *
     * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
     */
    const ROLES_TABLE_NAME_EB5 = 'sso_provider_roles_eb5';

    const FIELD_VALUE = 0;
    const FIELD_TYPE = 1;

    public function __construct(
        EntityManager $entityManager
    ) {
        $this->connection = $entityManager->getConnection();

        $this->spMetadata = $entityManager->getClassMetadata(ServiceProvider::class);
        $this->idpMetadata = $entityManager->getClassMetadata(IdentityProvider::class);

        /**
         * This code below has been added to temporary push to both sso_provider_roles_eb5
         * and sso_provider_roles_eb6
         *
         * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
         */
        $this->spMetadataUpdated = $entityManager->getClassMetadata(ServiceProviderEb5::class);
        $this->idpMetadataUpdated = $entityManager->getClassMetadata(IdentityProviderEb5::class);
    }

    /**
     * Synchronize the database with the provided roles.
     *
     * Any roles (idp or sp) already existing the database are updated. New
     * roles are created. All identity- or service providers in the database
     * which are NOT in the provided roles are deleted at the end of the
     * synchronization process.
     *
     * This method is optimised in order to prevent running into memory limits.
     * Therefore it doesn't use the ORM layer and only the DBAL layer is used.
     * The entity manager is only used to get the metadata of the entities.
     *
     * @param AbstractRole[] $roles
     * @return SynchronizationResult
     * @throws \Exception
     */
    public function synchronize(array $roles)
    {
        $result = new SynchronizationResult();

        $this->connection->transactional(function () use ($roles, $result) {
            $idpsToBeRemoved = $this->findAllRoleEntityIds($this->idpMetadata);
            $spsToBeRemoved = $this->findAllRoleEntityIds($this->spMetadata);

            foreach ($roles as $roleKey => $role) {
                if ($role instanceof IdentityProvider) {
                    // Does the IDP already exist in the database?
                    $index = array_search($role->entityId, $idpsToBeRemoved);

                    if ($index === false) {
                        // The IDP is new: create it.
                        $this->insertRole($role, $this->idpMetadata);
                        $result->createdIdentityProviders[] = $role->entityId;
                    } else {
                        // Remove from the list of entity ids so it won't get deleted later on.
                        unset($idpsToBeRemoved[$index]);

                        // The IDP already exists: update it.
                        $role->id = $index;
                        $this->updateRole($role, $this->idpMetadata);
                        $result->updatedIdentityProviders[] = $role->entityId;
                    }
                    unset($roles[$roleKey]);
                    continue;
                }

                if ($role instanceof ServiceProvider) {
                    // Does the SP already exist in the database?
                    $index = array_search($role->entityId, $spsToBeRemoved);
                    if ($index === false) {
                        // The SP is new: create it.
                        $this->insertRole($role, $this->spMetadata);
                        $result->createdServiceProviders[] = $role->entityId;
                    } else {
                        // Remove from the list of entity ids so it won't get deleted later on.
                        unset($spsToBeRemoved[$index]);

                        // The SP already exists: update it.
                        $role->id = $index;
                        $this->updateRole($role, $this->spMetadata);
                        $result->updatedServiceProviders[] = $role->entityId;
                    }
                    unset($roles[$roleKey]);
                    continue;
                }

                throw new RuntimeException(
                    sprintf('Unsupported role provided to synchronization: "%s"', var_export($role, true))
                );
            }

            if ($idpsToBeRemoved) {
                $this->deleteRolesByIds(array_keys($idpsToBeRemoved), $this->idpMetadata);
                /**
                 * This call {deleteRolesByEntityId} has been added to temporary push to both sso_provider_roles_eb5
                 * and sso_provider_roles_eb6
                 *
                 * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
                 */
                $this->deleteRolesByEntityId(array_values($idpsToBeRemoved), $this->idpMetadata);
                $result->removedIdentityProviders = array_values($idpsToBeRemoved);
            }

            if ($spsToBeRemoved) {
                $this->deleteRolesByIds(array_keys($spsToBeRemoved), $this->spMetadata);
                /**
                 * This call {deleteRolesByEntityId} has been added to temporary push to both sso_provider_roles_eb5
                 * and sso_provider_roles_eb6
                 *
                 * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
                 */
                $this->deleteRolesByEntityId(array_values($spsToBeRemoved), $this->spMetadata);
                $result->removedServiceProviders = array_values($spsToBeRemoved);
            }
        });

        return $result;
    }

    private function insertRole(AbstractRole $role, ClassMetadata $metadata)
    {
        $query = $this->connection->createQueryBuilder()
            ->insert(self::ROLES_TABLE_NAME);

        $normalized = $this->addInsertQueryParameters($role, $query, $metadata, false);

        $stmt = $this->connection->prepare($query->getSQL());
        $this->bindParameters($normalized, $stmt);
        $stmt->execute();

        /**
         * This code below has been added to temporary push to both sso_provider_roles_eb5 and sso_provider_roles_eb6
         *
         * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
         */
        $query = $this->connection->createQueryBuilder()
            ->insert(self::ROLES_TABLE_NAME_EB5);

        $normalized = $this->addInsertQueryParameters($role, $query, $metadata, true);

        $stmt = $this->connection->prepare($query->getSQL());
        $this->bindParameters($normalized, $stmt);
        $stmt->execute();
    }

    private function updateRole(AbstractRole $role, ClassMetadata $metadata)
    {
        $query = $this->connection->createQueryBuilder()
            ->update(self::ROLES_TABLE_NAME);

        $normalized = $this->addUpdateQueryParameters($role, $query, $metadata, false);

        $stmt = $this->connection->prepare($query->getSQL());
        $this->bindParameters($normalized, $stmt);
        $stmt->execute();

        /**
         * This code below has been added to temporary push to both sso_provider_roles_eb5 and sso_provider_roles_eb6
         *
         * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
         */
        $query = $this->connection->createQueryBuilder()
            ->update(self::ROLES_TABLE_NAME_EB5);

        $normalized = $this->addUpdateQueryParameters($role, $query, $metadata, true);

        $stmt = $this->connection->prepare($query->getSQL());
        $this->bindParameters($normalized, $stmt);
        $stmt->execute();
    }

    private function deleteRolesByIds(array $roles, ClassMetadata $metadata)
    {
        $query = $this->connection->createQueryBuilder()
            ->delete(self::ROLES_TABLE_NAME)
            ->where('id IN (:ids)')
            ->setParameter('ids', $roles, Connection::PARAM_INT_ARRAY);

        $this->addDiscriminatorQuery($query, $metadata);

        $result = $query->execute();
        return $result;
    }

    /**
     * This function has been added to temporary push to both sso_provider_roles_eb5 and sso_provider_roles_eb6
     *
     * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
     */
    private function deleteRolesByEntityId(array $entityIds, ClassMetadata $metadata)
    {
        $query = $this->connection->createQueryBuilder()
            ->delete(self::ROLES_TABLE_NAME_EB5)
            ->where('entity_id IN (:entity_ids)')
            ->setParameter('entity_ids', $entityIds, Connection::PARAM_STR_ARRAY);

        $this->addDiscriminatorQuery($query, $metadata);
        return $query->execute();
    }

    private function findAllRoleEntityIds(ClassMetadata $metadata)
    {
        $query = $this->connection->createQueryBuilder()
            ->select('id, entity_id')
            ->from(self::ROLES_TABLE_NAME);

        $this->addDiscriminatorQuery($query, $metadata);

        $result = $query->execute();
        $results = [];
        foreach ($result->fetchAll() as $record) {
            $results[$record['id']] = $record['entity_id'];
        }
        return $results;
    }

    /**
     * The code {$isInsertForDuplicateTable} has been added to temporary push to both sso_provider_roles_eb5
     * and sso_provider_roles_eb6
     *
     * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
     */
    private function addInsertQueryParameters(AbstractRole $role, QueryBuilder $query, ClassMetadata $metadata, bool $isInsertForDuplicateTable)
    {
        $normalized = $this->normalizeData($role, $metadata, $isInsertForDuplicateTable);
        foreach (array_keys($normalized) as $id) {
            $query->setValue($id, ":$id");
        }
        return $normalized;
    }

    /**
     * The code {$isUpdateForDuplicateTable} has been added to temporary push to both sso_provider_roles_eb5
     * and sso_provider_roles_eb6
     *
     * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
     */
    private function addUpdateQueryParameters(AbstractRole $role, QueryBuilder $query, ClassMetadata $metadata, bool $isUpdateForDuplicateTable)
    {
        $normalized = $this->normalizeData($role, $metadata, $isUpdateForDuplicateTable);
        foreach (array_keys($normalized) as $id) {
            $query->set($id, ":$id");
        }
        $query->where('entity_id = :entity_id');

        $this->addDiscriminatorQuery($query, $metadata);

        return $normalized;
    }

    private function bindParameters($normalized, Statement $statement)
    {
        foreach ($normalized as $id => $value) {
            $statement->bindValue($id, $value[self::FIELD_VALUE], $value[self::FIELD_TYPE]);
        }
    }

    private function addDiscriminatorQuery(QueryBuilder $queryBuilder, ClassMetadata $metadata)
    {
        $queryBuilder->andWhere(
            sprintf('%s = :%s', $metadata->discriminatorColumn['fieldName'], $metadata->discriminatorColumn['name'])
        )->setParameter(
            $metadata->discriminatorColumn['name'],
            $metadata->discriminatorValue,
            $metadata->discriminatorColumn['type']
        );
    }

    private function normalizeData(AbstractRole $role, ClassMetadata $metadata, bool $isNormalizeForDuplicateTable)
    {
        $result = [];
        /**
         * The code {$isNormalizeForDuplicateTable} has been added to temporary push to both sso_provider_roles_eb5
         * and sso_provider_roles_eb6
         *
         * TODO: Remove this code after sso_provider_roles_eb5 has been phased out
         */
        if ($isNormalizeForDuplicateTable) {
            if ($role instanceof IdentityProvider) {
                foreach ($metadata->fieldMappings as $id => $columnInfo) {
                    $result[$columnInfo['columnName']] = [
                        self::FIELD_VALUE => $metadata->reflFields[$id]->getValue($role),
                        self::FIELD_TYPE => $this->idpMetadataUpdated->fieldMappings[$id]['type'],
                    ];
                }
            }
            if ($role instanceof ServiceProvider) {
                foreach ($metadata->fieldMappings as $id => $columnInfo) {
                    $result[$columnInfo['columnName']] = [
                        self::FIELD_VALUE => $metadata->reflFields[$id]->getValue($role),
                        self::FIELD_TYPE => $this->spMetadataUpdated->fieldMappings[$id]['type'],
                    ];
                }
            }
        } else {
            foreach ($metadata->fieldMappings as $id => $columnInfo) {
                $result[$columnInfo['columnName']] = [
                    self::FIELD_VALUE => $metadata->reflFields[$id]->getValue($role),
                    self::FIELD_TYPE => $columnInfo['type'],
                ];
            }
        }

        // The primary id field is autogenerated and should not be added to the SQL statement.
        unset($result["id"]);

        $result[$metadata->discriminatorColumn['name']] = [
            self::FIELD_VALUE => $metadata->discriminatorValue,
            self::FIELD_TYPE => $metadata->discriminatorColumn['type'],
        ];

        return $result;
    }
}
