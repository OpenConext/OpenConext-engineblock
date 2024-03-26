<?php

namespace OpenConext\EngineBlockBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;

class MetadataPushVoter implements VoterInterface
{
    private const PUSH_ROLE = "ROLE_API_USER_METADATA_PUSH";

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute == self::PUSH_ROLE;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return null != $token->getUser() && in_array(self::PUSH_ROLE, $token->getUser()->getRoles()) &&
            $this->security->isGranted(self::PUSH_ROLE);
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            $attribute = $attribute[0];
            if (!$this->supports($attribute, $subject)) {
                continue;
            }

            $vote = self::ACCESS_DENIED;

            if ($this->voteOnAttribute($attribute, $subject, $token)) {
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }
}
