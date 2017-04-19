<?php

namespace OpenConext\EngineBlock\Service;

use Exception;
use OpenConext\EngineBlock\Authentication\Dto\Consent;
use OpenConext\EngineBlock\Authentication\Dto\ConsentList;
use OpenConext\EngineBlock\Authentication\Model\Consent as ConsentEntity;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\Value\Saml\EntityId;
use Psr\Log\LoggerInterface;

final class ConsentService
{
    /**
     * @var ConsentRepository
     */
    private $consentRepository;

    /**
     * @var MetadataService
     */
    private $metadataService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConsentRepository $consentRepository,
        MetadataService $metadataService,
        LoggerInterface $logger
    ) {
        $this->consentRepository = $consentRepository;
        $this->metadataService   = $metadataService;
        $this->logger            = $logger;
    }

    /**
     * @param string $userId
     * @return ConsentList
     */
    public function findAllFor($userId)
    {
        try {
            $consents = $this->consentRepository->findAllFor($userId);
        } catch (Exception $e) {
            throw new RuntimeException(
                sprintf('An exception occurred while fetching consents the user has given ("%s")', $e->getMessage()),
                0,
                $e
            );
        }

        return new ConsentList(array_filter(array_map([$this, 'createConsentDtoFromConsentEntity'], $consents)));
    }

    /**
     * @param ConsentEntity $consent
     * @return Consent|null
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) it is used as callable in the findForAll() method
     */
    private function createConsentDtoFromConsentEntity(ConsentEntity $consent)
    {
        $entityId        = $consent->getServiceProviderEntityId();
        $serviceProvider = $this->metadataService->findServiceProvider(new EntityId($entityId));

        if ($serviceProvider === null) {
            return null;
        }

        return new Consent($consent, $serviceProvider);
    }
}
