<?php

/**
 * Copyright 2026 SURFnet B.V.
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

declare(strict_types=1);

namespace OpenConext\EngineBlockBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

final class RememberChoiceDurationFormatter
{
    private const SECONDS_PER_MINUTE = 60;
    private const SECONDS_PER_DAY = 86400;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly int $lifetimeInSeconds,
    ) {
    }

    public function format(): string
    {
        if ($this->lifetimeInSeconds < self::SECONDS_PER_DAY) {
            $minutes = intdiv($this->lifetimeInSeconds, self::SECONDS_PER_MINUTE);
            return $this->translator->trans('remember_choice_duration_minutes', ['%count%' => $minutes]);
        }

        $days = intdiv($this->lifetimeInSeconds, self::SECONDS_PER_DAY);
        return $this->translator->trans('remember_choice_duration_days', ['%count%' => $days]);
    }
}
