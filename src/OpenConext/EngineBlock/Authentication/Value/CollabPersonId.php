<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use EngineBlock_UserDirectory as UserDirectory;
use OpenConext\EngineBlock\Assert\Assertion;

final class CollabPersonId
{
    /**
     * @var string
     */
    private $collabPersonId;

    /**
     * @param string $collabPersonId
     */
    public function __construct($collabPersonId)
    {
        Assertion::nonEmptyString($collabPersonId, 'collabPersonId');
        Assertion::startsWith(
            $collabPersonId,
            UserDirectory::URN_COLLAB_PERSON_NAMESPACE,
            sprintf('a CollabPersonId must start with the "%" namespace', UserDirectory::URN_COLLAB_PERSON_NAMESPACE)
        );

        $this->collabPersonId = $collabPersonId;
    }

    /**
     * @return string
     */
    public function getCollabPersonId()
    {
        return $this->collabPersonId;
    }

    /**
     * @param CollabPersonId $other
     * @return bool
     */
    public function equals(CollabPersonId $other)
    {
        return $this->collabPersonId === $other->collabPersonId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('CollabPersonId(%s)', $this->collabPersonId);
    }
}
