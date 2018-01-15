<?php

namespace OpenConext\EngineBlock\Metadata\Repository;

use OpenConext\Value\Saml\EntityId;

class MetadataRepository
{
    /**
     * @var SamlEntityRepository
     */
    private $samlEntityRepository;

    /**
     * @var ConnectionRepository
     */
    private $connectionRepository;

    public function __construct(
        SamlEntityRepository $samlEntityRepository,
        ConnectionRepository $connectionRepository
    ) {
        $this->samlEntityRepository = $samlEntityRepository;
        $this->connectionRepository = $connectionRepository;
    }
}
