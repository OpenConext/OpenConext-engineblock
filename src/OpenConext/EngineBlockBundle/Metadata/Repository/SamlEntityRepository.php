<?php

namespace OpenConext\EngineBlockBundle\Metadata\Entity;

use Doctrine\ORM\EntityRepository;
use OpenConext\EngineBlock\Metadata\Repository\SamlEntityRepository as SamlEntityRepositoryInterface;

class SamlEntityRepository extends EntityRepository implements SamlEntityRepositoryInterface
{
}
