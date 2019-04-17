<?php
/**
 * Copyright 2019 SURFnet B.V.
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
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use RuntimeException;

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

    const FIELD_VALUE = 0;
    const FIELD_TYPE = 1;

    public function __construct(
        EntityManager $entityManager
    ) {
        $this->connection = $entityManager->getConnection();

        $this->spMetadata = $entityManager->getClassMetadata(ServiceProvider::class);
        $this->idpMetadata = $entityManager->getClassMetadata(IdentityProvider::class);
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
            $idpsToBeRemoved = $this->findAllIdentityProviderEntityIds();
            $spsToBeRemoved = $this->findAllServiceProviderEntityIds();

            foreach ($roles as $roleKey => $role) {
                if ($role instanceof IdentityProvider) {
                    // Does the IDP already exist in the database?
                    $index = array_search($role->entityId, $idpsToBeRemoved);

                    if ($index === false) {
                        // The IDP is new: create it.
                        $this->insertIdentityProvider($role);
                        $result->createdIdentityProviders[] = $role->entityId;
                    } else {
                        // Remove from the list of entity ids so it won't get deleted later on.
                        unset($idpsToBeRemoved[$index]);

                        // The IDP already exists: update it.
                        $role->id = $index;
                        $this->updateIdentityProvider($role);
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
                        $this->insertServiceProvider($role);
                        $result->createdServiceProviders[] = $role->entityId;
                    } else {
                        // Remove from the list of entity ids so it won't get deleted later on.
                        unset($spsToBeRemoved[$index]);

                        // The SP already exists: update it.
                        $role->id = $index;
                        $this->updateServiceProvider($role);
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
                $this->deleteRolesByEntityIds($idpsToBeRemoved);

                $result->removedIdentityProviders = array_values($idpsToBeRemoved);
            }

            if ($spsToBeRemoved) {
                $this->deleteRolesByEntityIds($spsToBeRemoved);

                $result->removedServiceProviders = array_values($spsToBeRemoved);
            }
        });

        return $result;
    }

    private function insertServiceProvider(ServiceProvider $role)
    {
        $query = $this->connection->createQueryBuilder()
            ->insert('sso_provider_roles_eb5');

        $normalized = $this->addInsertQueryParameters($role, $query, $this->spMetadata);

        $stmt = $this->connection->prepare($query->getSQL());
        $this->bindParameters($normalized, $stmt);
        $stmt->execute();
    }

    private function updateServiceProvider(ServiceProvider $role)
    {
        $query = $this->connection->createQueryBuilder()
            ->update('sso_provider_roles_eb5');

        $normalized = $this->addUpdateQueryParameters($role, $query, $this->spMetadata);

        $stmt = $this->connection->prepare($query->getSQL());
        $this->bindParameters($normalized, $stmt);
        $stmt->execute();
    }

    private function insertIdentityProvider(IdentityProvider $role)
    {
        $query = $this->connection->createQueryBuilder()
            ->insert('sso_provider_roles_eb5');

        $normalized = $this->addInsertQueryParameters($role, $query, $this->idpMetadata);

        $stmt = $this->connection->prepare($query->getSQL());
        $this->bindParameters($normalized, $stmt);
        $stmt->execute();
    }

    private function updateIdentityProvider(IdentityProvider $role)
    {
        $query = $this->connection->createQueryBuilder()
            ->update('sso_provider_roles_eb5');

        $normalized = $this->addUpdateQueryParameters($role, $query, $this->idpMetadata);

        $stmt = $this->connection->prepare($query->getSQL());
        $this->bindParameters($normalized, $stmt);
        $stmt->execute();
    }

    private function deleteRolesByEntityIds(array $roles)
    {
        $query = $this->connection->createQueryBuilder()
            ->delete('sso_provider_roles_eb5')
            ->where('entity_id IN (:ids)')
            ->setParameter('ids', $roles, Connection::PARAM_INT_ARRAY);

        $result = $query->execute();
        return $result;
    }

    private function findAllIdentityProviderEntityIds()
    {
        $query = $this->connection->createQueryBuilder()
            ->select('id, entity_id')
            ->from('sso_provider_roles_eb5')
            ->where('type="idp"');

        $result = $query->execute();
        $results = [];
        foreach ($result->fetchAll() as $record) {
            $results[$record['id']] = $record['entity_id'];
        }
        return $results;
    }

    private function findAllServiceProviderEntityIds()
    {
        $query = $this->connection->createQueryBuilder()
            ->select('id, entity_id')
            ->from('sso_provider_roles_eb5')
            ->where('type="sp"');

        $result = $query->execute();
        $results = [];
        foreach ($result->fetchAll() as $record) {
            $results[$record['id']] = $record['entity_id'];
        }
        return $results;
    }

    private function addInsertQueryParameters(AbstractRole $role, QueryBuilder $query, ClassMetadata $metadata)
    {
        $normalized = $this->normalizeData($role, $metadata);
        foreach (array_keys($normalized) as $id) {
            $query->setValue($id, ":$id");
        }
        return $normalized;
    }

    private function addUpdateQueryParameters(AbstractRole $role, QueryBuilder $query, ClassMetadata $metadata)
    {
        $normalized = $this->normalizeData($role, $metadata);
        foreach (array_keys($normalized) as $id) {
            $query->set($id, ":$id");
        }
        $query->where('entity_id = :entity_id AND type = :type');
        return $normalized;
    }

    private function bindParameters($normalized, Statement $statement)
    {
        foreach ($normalized as $id => $value) {
            $statement->bindValue($id, $value[self::FIELD_VALUE], $value[self::FIELD_TYPE]);
        }
    }

    private function normalizeData(AbstractRole $role, ClassMetadata $metadata)
    {
        $result = [];
        foreach ($metadata->fieldMappings as $id => $columnInfo) {
            $result[$columnInfo['columnName']] = [
                self::FIELD_VALUE => $metadata->reflFields[$id]->getValue($role),
                self::FIELD_TYPE => $columnInfo['type'],
            ];
        }

        $result[$metadata->discriminatorColumn['name']] = [
            self::FIELD_VALUE => $metadata->discriminatorValue,
            self::FIELD_TYPE => $metadata->discriminatorColumn['type'],
        ];

        return $result;
    }
}
