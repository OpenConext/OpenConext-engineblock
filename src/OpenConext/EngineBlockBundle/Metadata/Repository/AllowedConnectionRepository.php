<?php

namespace OpenConext\EngineBlockBundle\Metadata\Entity;

use Doctrine\ORM\EntityRepository;
use OpenConext\EngineBlock\Metadata\Repository\ConnectionRepository;

class AllowedConnectionRepository extends EntityRepository implements ConnectionRepository
{
}
