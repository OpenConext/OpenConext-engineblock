<?php

namespace OpenConext\EngineBlock\ApiBundle\Service;

use Doctrine\DBAL\DBALException;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\EntityNotFoundException;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\ApiBundle\Dto\Consent;
use OpenConext\EngineBlock\ApiBundle\Dto\ConsentList;
use OpenConext\EngineBlock\ApiBundle\Exception\RuntimeException;
use OpenConext\EngineBlock\Authentication\Entity\Consent as ConsentEntity;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use Psr\Log\LoggerInterface;

final class ConsentService
{
    /**
     * @var ConsentRepository
     */
    private $consentRepository;

    /**
     * @var MetadataRepositoryInterface
     */
    private $metadataRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConsentRepository $consentRepository,
        MetadataRepositoryInterface $metadataRepository,
        LoggerInterface $logger
    ) {
        $this->consentRepository  = $consentRepository;
        $this->metadataRepository = $metadataRepository;
        $this->logger             = $logger;
    }

    /**
     * @param string $userId
     * @return ConsentList
     */
    public function findAllFor($userId)
    {
        try {
            $consents = $this->consentRepository->findAllFor($userId);
        } catch (DBALException $e) {
            throw new RuntimeException(
                sprintf('An exception occurred while fetching consents the user has given ("%s")', $e->getMessage()),
                0,
                $e
            );
        }

        return new ConsentList(array_filter(array_map(array($this, 'createConsentDtoFromConsentEntity'), $consents)));
    }

    /**
     * @param ConsentEntity $consent
     * @return Consent|null
     */
    private function createConsentDtoFromConsentEntity(ConsentEntity $consent)
    {
        $entityId = $consent->getServiceProviderEntityId();

        try {
            $serviceProvider = $this->metadataRepository->fetchServiceProviderByEntityId($entityId);
        } catch (EntityNotFoundException $e) {
            $this->logger->warning(
                sprintf(
                    'Metadata for service provider "%s" could not be retrieved for inclusion with consent API result',
                    $entityId
                )
            );

            return null;
        }

        return new Consent($consent, $serviceProvider);
    }
}
