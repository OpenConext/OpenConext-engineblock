<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use OpenConext\EngineBlock\Metadata\Container\ContainerInterface;
use OpenConext\EngineBlock\Metadata\JanusRestV1\RestClientDecorator;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Entity\Assembler\JanusRestV1Assembler;
use OpenConext\EngineBlock\Metadata\JanusRestV1\RestClientInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Class JanusRestV1MetadataRepository
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.TooManyPublicMethods)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class JanusRestV1MetadataRepository extends AbstractMetadataRepository
{
    /**
     * @var RestClientDecorator
     */
    private $client;

    /**
     * @var JanusRestV1Assembler
     */
    private $assembler;

    /**
     * @var RuntimeException
     */
    private $prevClientException = null;

    /**
     * @var array
     */
    private $entityCache = array();

    /**
     * @param RestClientInterface $client
     * @param JanusRestV1Assembler $assembler
     */
    public function __construct(RestClientInterface $client, JanusRestV1Assembler $assembler)
    {
        parent::__construct();

        $this->client = new RestClientDecorator($client);
        $this->assembler = $assembler;
    }

    /**
     * @param array $repositoryConfig
     * @param ContainerInterface $container
     * @return mixed
     */
    public static function createFromConfig(array $repositoryConfig, ContainerInterface $container)
    {
        return new static(
            $container->getServiceRegistryClient(),
            new JanusRestV1Assembler()
        );
    }

    /**
     *
     * @param string $entityId
     * @return AbstractRole
     * @throws EntityNotFoundException
     */
    public function fetchEntityByEntityId($entityId)
    {
        $entity = $this->findEntityByEntityId($entityId);

        if (!$entity) {
            throw new EntityNotFoundException(
                "Unable to find entity for entityId '$entityId' ",
                null,
                $this->prevClientException
            );
        }

        return $entity;
    }

    /**
     * @param string $entityId
     * @return ServiceProvider
     * @throws EntityNotFoundException
     */
    public function fetchServiceProviderByEntityId($entityId)
    {
        $entity = $this->findServiceProviderByEntityId($entityId);

        if (!$entity) {
            throw new EntityNotFoundException(
                "Unable to find entity for entityId '$entityId' ",
                null,
                $this->prevClientException
            );
        }

        return $entity;
    }

    /**
     * @param $entityId
     * @return null|IdentityProvider|ServiceProvider
     * @throws EntityNotFoundException
     */
    public function fetchIdentityProviderByEntityId($entityId)
    {
        $entity = $this->findIdentityProviderByEntityId($entityId);

        if (!$entity) {
            throw new EntityNotFoundException(
                "Unable to find entity for entityId '$entityId' ",
                null,
                $this->prevClientException
            );
        }

        return $entity;
    }

    /**
     * @param string $entityId
     * @return AbstractRole|null
     */
    public function findEntityByEntityId($entityId)
    {
        // If we have it in our entity cache...
        if (isset($this->entityCache[$entityId])) {
            return $this->postProcessCachedEntity($entityId);
        }

        // If not, use the client to find metadata.
        $metadata = $this->client->findMetadataByEntityId($entityId);
        if (!$metadata) {
            return null;
        }

        // Assemble an entity out of it.
        $entity = $this->assembler->assemble($entityId, $metadata);
        if (!$entity) {
            return null;
        }

        // Cache it.
        $this->entityCache[$entityId] = $entity;

        return $this->postProcessCachedEntity($entityId);
    }

    /**
     * @param string $entityId
     * @return null|AbstractRole|ServiceProvider
     * @throws EntityNotFoundException
     */
    public function findIdentityProviderByEntityId($entityId)
    {
        if (isset($this->entityCache[$entityId])) {
            return $this->postProcessCachedEntity($entityId);
        }

        $metadata = $this->client->findIdentityProviderMetadataByEntityId($entityId);
        if (empty($metadata)) {
            $this->entityCache[$entityId] = null;
            return null;
        }

        $entity = $this->assembler->assemble($entityId, $metadata);
        if (!$entity) {
            $this->entityCache[$entityId] = null;
            return null;
        }

        if (!$entity instanceof IdentityProvider) {
            $this->entityCache[$entityId] = null;
            return null;
        }

        $this->entityCache[$entityId] = $entity;
        return $this->postProcessCachedEntity($entityId);
    }

    /**
     * @param $entityId
     * @param LoggerInterface|null $logger
     * @return null|ServiceProvider
     */
    public function findServiceProviderByEntityId($entityId, LoggerInterface $logger = null)
    {
        $metadata = $this->client->findServiceProviderMetadataByEntityId($entityId);
        if (empty($metadata)) {
            $this->entityCache[$entityId] = null;
            return null;
        }

        $entity = $this->assembler->assemble($entityId, $metadata);

        if (!$entity) {
            $this->entityCache[$entityId] = null;
            return null;
        }

        if (!$entity instanceof ServiceProvider) {
            $this->entityCache[$entityId] = null;
            return null;
        }

        $this->entityCache[$entityId] = $entity;

        return $this->postProcessCachedEntity($entityId, $logger);
    }

    /**
     * @return array|IdentityProvider[]
     * @throws \RuntimeException
     */
    public function findIdentityProviders()
    {
        $entities = $this->client->getIdpList();

        $identityProviders = array();
        foreach ($entities as $entityId => $entity) {
            if (!isset($this->entityCache[$entityId])) {
                $entity = $this->assembler->assemble($entityId, $entity);

                if (!is_null($entity) && !$entity instanceof IdentityProvider) {
                    throw new \RuntimeException('Service Registry returned a non-idp from getIdpList?');
                }

                $this->entityCache[$entityId] = $entity;
            }

            if (!$this->entityCache[$entityId]) {
                continue;
            }

            $entity = $this->postProcessCachedEntity($entityId);

            if (!$entity) {
                continue;
            }

            $identityProviders[$entityId] = $entity;
        }
        return $identityProviders;
    }

    /**
     * @return AbstractRole[]
     */
    public function findEntitiesPublishableInEdugain(MetadataRepositoryInterface $repository = null)
    {
        if (!$repository) {
            $repository = $this;
        }
        $entityIds = $this->client->findIdentifiersByMetadata('coin:publish_in_edugain', '1');

        $publishable = array();
        foreach ($entityIds as $entityId) {
            $publishable[] = $repository->fetchEntityByEntityId($entityId);
        }
        return $publishable;
    }

    /**
     * @param AbstractRole $entity
     * @return string
     * @throws EntityNotFoundException
     */
    public function fetchEntityManipulation(AbstractRole $entity)
    {
        $entityData = $this->client->getEntity($entity->entityId);

        return $entityData['manipulation'];
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @return AttributeReleasePolicy
     */
    public function fetchServiceProviderArp(ServiceProvider $serviceProvider)
    {
        $entityData = $this->client->getEntity($serviceProvider->entityId);

        if ($entityData['arp'] === null) {
            return null;
        }

        return new AttributeReleasePolicy($entityData['arp']);
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @return bool
     */
    public function findAllowedIdpEntityIdsForSp(ServiceProvider $serviceProvider)
    {
        return $this->client->getAllowedIdps($serviceProvider->entityId);
    }

    /**
     * @param $entityId
     * @return null|AbstractRole
     */
    private function postProcessCachedEntity($entityId, LoggerInterface $logger = null)
    {
        // Filter
        $entity = $this->compositeFilter->filterRole($this->entityCache[$entityId], $logger);
        if (!$entity) {
            return null;
        }

        // Visit
        $entity->accept($this->compositeVisitor);

        return $entity;
    }
}
