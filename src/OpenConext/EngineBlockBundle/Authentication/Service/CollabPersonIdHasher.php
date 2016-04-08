<?php

namespace OpenConext\EngineBlockBundle\Authentication\Service;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;

class CollabPersonIdHasher
{
    /**
     * @var string
     */
    private $hashingAlgorithm;

    /**
     * @param string $hashingAlgorithm
     */
    public function __construct($hashingAlgorithm)
    {
        Assertion::validHashingAlgorithm($hashingAlgorithm);

        $this->hashingAlgorithm = $hashingAlgorithm;
    }

    /**
     * @param CollabPersonId $collabPersonId
     * @return string
     */
    public function hash(CollabPersonId $collabPersonId)
    {
        return hash($this->hashingAlgorithm, $collabPersonId->getCollabPersonId());
    }
}
