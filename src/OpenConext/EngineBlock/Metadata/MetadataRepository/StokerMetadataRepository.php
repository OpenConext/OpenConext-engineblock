<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use OpenConext\EngineBlock\Metadata\Container\ContainerInterface;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Entity\Assembler\StokerAssembler;
use OpenConext\Component\StokerMetadata\MetadataEntitySource;
use OpenConext\Component\StokerMetadata\MetadataIndex;
use Psr\Log\LoggerInterface;

/**
 * Class StokerMetadataRepository
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class StokerMetadataRepository extends AbstractMetadataRepository
{
    /**
     * @var StokerAssembler
     */
    private $translator;

    /**
     * @var MetadataEntitySource
     */
    private $metadataEntitySource;

    /**
     * @var MetadataIndex
     */
    private $metadataIndex;

    /**
     * @param array $repositoryConfig
     * @param ContainerInterface $container
     * @return mixed|static
     * @throws \RuntimeException
     */
    public static function createFromConfig(array $repositoryConfig, ContainerInterface $container)
    {
        if (!isset($repositoryConfig['path'])) {
            throw new \RuntimeException('No path configured for stoker repository');
        }
        $directory = $repositoryConfig['path'];

        $entitySource = new MetadataEntitySource($directory);

        $index = MetadataIndex::load($directory);
        if (!$index) {
            throw new \RuntimeException(
                "Unable to load $directory" . DIRECTORY_SEPARATOR . MetadataIndex::FILENAME
            );
        }

        $assembler = new StokerAssembler();

        return new static($entitySource, $index, $directory, $assembler);
    }

    /**
     * @param MetadataEntitySource $source
     * @param MetadataIndex $index
     * @param $directory
     * @param StokerAssembler $assembler
     */
    public function __construct(
        MetadataEntitySource $source,
        MetadataIndex $index,
        $directory,
        StokerAssembler $assembler
    ) {
        parent::__construct();

        $this->metadataEntitySource = $source;
        $this->metadataIndex = $index;
        $this->metadataDirectory = $directory;
        $this->translator = $assembler;
    }

    /**
     *
     * @param string $entityId
     * @return AbstractRole
     * @throws EntityNotFoundException
     */
    public function fetchEntityByEntityId($entityId)
    {
        $metadataIndexEntity = $this->metadataIndex->getEntityByEntityId($entityId);
        if (!$metadataIndexEntity) {
            throw new EntityNotFoundException("Unable to find entity in the index");
        }

        $xml = $this->metadataEntitySource->load($entityId);
        if (empty($xml)) {
            throw new EntityNotFoundException("Unable to find entity for '$entityId'");
        }

        $entity = $this->translator->assemble($xml, $metadataIndexEntity);

        $entity = $this->compositeFilter->filterRole($entity);
        if (!$entity) {
            throw new EntityNotFoundException(
                "Found entity for '$entityId', but disallowed by filter: " .
                $this->compositeFilter->getDisallowedByFilter()
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
        $entity = $this->fetchEntityByEntityId($entityId);

        if (!$entity instanceof ServiceProvider) {
            throw new EntityNotFoundException("Entity found for '$entityId' is not a Service Provider");
        }

        return $entity;
    }

    /**
     * @param string $entityId
     * @return AbstractRole|null
     */
    public function findEntityByEntityId($entityId)
    {
        $metadataIndexEntity = $this->metadataIndex->getEntityByEntityId($entityId);
        if (!$metadataIndexEntity) {
            return null;
        }

        $xml = $this->metadataEntitySource->load($entityId);
        if (empty($xml)) {
            // @todo warn, the index has an entity that is not on disk?
            return null;
        }

        $entity = $this->translator->assemble($xml, $metadataIndexEntity);

        $entity = $this->compositeFilter->filterRole($entity);
        if (!$entity) {
            return null;
        }

        return $entity;
    }

    /**
     * @param string $entityId
     * @return ServiceProvider|null
     */
    public function findIdentityProviderByEntityId($entityId)
    {
        $entity = $this->findEntityByEntityId($entityId);

        if (!$entity instanceof IdentityProvider) {
            return null;
        }

        return $entity;
    }

    /**
     * @param $entityId
     * @param LoggerInterface|null $logger
     * @return null|ServiceProvider
     */
    public function findServiceProviderByEntityId($entityId, LoggerInterface $logger = null)
    {
        $entity = $this->findEntityByEntityId($entityId);

        if (!$entity instanceof ServiceProvider) {
            return null;
        }

        return $entity;
    }

    /**
     * @return IdentityProvider[]
     */
    public function findIdentityProviders()
    {
        $entities = $this->metadataIndex->getEntities();
        $identityProviders = array();
        foreach ($entities as $metadataIndexEntity) {
            if (!in_array(MetadataIndex\Entity::TYPE_IDP, $metadataIndexEntity->types)) {
                continue;
            }

            $entityXml = $this->metadataEntitySource->load($metadataIndexEntity->entityId);
            if (!$entityXml) {
                // @todo warn
                continue;
            }

            $entity = $this->translator->assemble($entityXml, $metadataIndexEntity);

            $entity = $this->compositeFilter->filterRole($entity);
            if (!$entity) {
                // @todo warn
                continue;
            }

            $identityProviders[$entity->entityId] = $entity;
        }
        return $identityProviders;
    }

    /**
     * @return AbstractRole[]
     */
    public function findEntitiesPublishableInEdugain(MetadataRepositoryInterface $repository = null)
    {
        return array();
    }
}
