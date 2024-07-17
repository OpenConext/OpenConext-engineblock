<?php

/**
 * Copyright 2024 Stichting Kennisnet
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlockBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Security;

class MetadataPushVoter implements VoterInterface
{
    const PUSH_ROLE = "ROLE_API_USER_METADATA_PUSH";

    /**
     * @var Security
     */
    private $security;

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
