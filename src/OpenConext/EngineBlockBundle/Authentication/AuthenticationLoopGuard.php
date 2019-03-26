<?php

/**
 * Copyright 2016 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Authentication;

use DateTimeImmutable;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\Value\Saml\Entity;

final class AuthenticationLoopGuard implements AuthenticationLoopGuardInterface
{
    /**
     * @var int
     */
    private $maximumAuthenticationProceduresAllowed;

    /**
     * @var int
     */
    private $timeFrameForAuthenticationLoopInSeconds;

    public function __construct(
        $maximumAuthenticationProceduresAllowed,
        $timeFrameForAuthenticationLoopInSeconds
    ) {
        Assertion::integer(
            $maximumAuthenticationProceduresAllowed,
            'Expected maximum authentication procedures allowed to be an integer, got "%s"'
        );
        Assertion::integer(
            $timeFrameForAuthenticationLoopInSeconds,
            'Expected time frame for determining authentication loop in seconds to be an integer, got "%s"'
        );

        $this->maximumAuthenticationProceduresAllowed  = $maximumAuthenticationProceduresAllowed;
        $this->timeFrameForAuthenticationLoopInSeconds = $timeFrameForAuthenticationLoopInSeconds;
    }

    /**
     * @param Entity $serviceProvider
     * @param AuthenticationProcedureMap $pastAuthenticationProcedures
     * @return bool
     */
    public function detectsAuthenticationLoop(
        Entity $serviceProvider,
        AuthenticationProcedureMap $pastAuthenticationProcedures
    ) {
        $now  = new DateTimeImmutable;
        $startDate = $now->modify(sprintf('-%d seconds', $this->timeFrameForAuthenticationLoopInSeconds));

        $processedLoginProcedures = $pastAuthenticationProcedures
            ->filterOnBehalfOf($serviceProvider)
            ->filterProceduresCompletedAfter($startDate)
            ->count();

        return $processedLoginProcedures >= $this->maximumAuthenticationProceduresAllowed;
    }
}
