<?php

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Assert\Assertion;

final class CollabPersonId
{
    /**
     * Required namespace prefix
     */
    const URN_NAMESPACE = 'urn:collab:person';

    /**
     * Max length of the CollabPersonId.
     */
    const MAX_LENGTH = 255;

    /**
     * @var string
     */
    private $collabPersonId;

    /**
     * @param Uid                   $uid
     * @param SchacHomeOrganization $schacHomeOrganization
     * @return CollabPersonId
     */
    public static function generateFrom(Uid $uid, SchacHomeOrganization $schacHomeOrganization)
    {
        $collabPersonId = implode(
            ':',
            [self::URN_NAMESPACE, $schacHomeOrganization->getSchacHomeOrganization(), $uid->getUid()]
        );

        return new self($collabPersonId);
    }

    /**
     * @param string $collabPersonId
     */
    public function __construct($collabPersonId)
    {
        Assertion::nonEmptyString($collabPersonId, 'collabPersonId');
        Assertion::startsWith(
            $collabPersonId,
            self::URN_NAMESPACE,
            sprintf('a CollabPersonId must start with the "%s" namespace', self::URN_NAMESPACE)
        );
        Assertion::maxLength($collabPersonId, self::MAX_LENGTH, 'CollabPersonId length may not exceed 400 characters');

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
