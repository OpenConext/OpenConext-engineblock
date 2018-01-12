<?php

namespace OpenConext\EngineBlockBundle\Metadata\Service;

use OpenConext\EngineBlockBundle\Metadata\Repository\InMemoryAllowedConnectionRepository;
use OpenConext\EngineBlockBundle\Metadata\Repository\InMemorySamlServiceRepository;

class MetadataService
{
    /**
     * @var MetadataMarshallingService
     */
    private $metadataMarshallingService;

    /**
     * @var InMemorySamlServiceRepository
     */
    private $samlServiceRepository;

    /**
     * @var InMemoryAllowedConnectionRepository
     */
    private $allowedConnectionRepository;

    public function __construct(
        MetadataMarshallingService $metadataMarshallingService,
        InMemorySamlServiceRepository $samlServiceRepository,
        InMemoryAllowedConnectionRepository $allowedConnectionRepository
    ) {
        $this->metadataMarshallingService = $metadataMarshallingService;
        $this->samlServiceRepository = $samlServiceRepository;
        $this->allowedConnectionRepository = $allowedConnectionRepository;
    }

    public function applyNewConfiguration(array $metadataConfiguration)
    {

    }
}
