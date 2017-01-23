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
use OpenConext\EngineBlockBundle\Exception\StuckInAuthenticationLoopException;
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
     * @param AuthenticationProcedureList $pastAuthenticationProcedures
     */
    public function assertNotStuckInLoop(
        Entity $serviceProvider,
        AuthenticationProcedureList $pastAuthenticationProcedures
    ) {
        $dateTime  = new DateTimeImmutable;
        $startDate = $dateTime->modify(sprintf('-%d seconds', $this->timeFrameForAuthenticationLoopInSeconds));

        $relevantProceduresInTimeFrame = $pastAuthenticationProcedures
            ->findOnBehalfOf($serviceProvider)
            ->findProceduresCompletedAfter($startDate);

        $stuckInAuthenticationLoop = count($relevantProceduresInTimeFrame) >= $this->maximumAuthenticationProceduresAllowed;

        if ($stuckInAuthenticationLoop) {
            throw new StuckInAuthenticationLoopException(
                sprintf(
                    'After %d authentication procedures, we determined within a time frame of %d seconds'
                    . ' that we are stuck in an authentication loop for service provider "%s"',
                    $this->maximumAuthenticationProceduresAllowed,
                    $this->timeFrameForAuthenticationLoopInSeconds,
                    $serviceProvider->getEntityId()
                )
            );
        }
    }
}
