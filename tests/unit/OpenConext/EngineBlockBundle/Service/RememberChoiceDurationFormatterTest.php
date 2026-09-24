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

namespace Tests\OpenConext\EngineBlockBundle\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\Service\RememberChoiceDurationFormatter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class RememberChoiceDurationFormatterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[DataProvider('lifetimeProvider')]
    public function testFormat(int $lifetimeInSeconds, string $expectedKey, array $expectedParameters): void
    {
        $translator = m::mock(TranslatorInterface::class);
        $translator->shouldReceive('trans')
            ->once()
            ->with($expectedKey, $expectedParameters)
            ->andReturn('formatted-duration');

        $formatter = new RememberChoiceDurationFormatter($translator, $lifetimeInSeconds);

        $this->assertSame('formatted-duration', $formatter->format());
    }

    public static function lifetimeProvider(): array
    {
        return [
            'ninety days (default lifetime)' => [7776000, 'remember_choice_duration_days', ['%count%' => 90]],
            'exactly one day'                => [86400, 'remember_choice_duration_days', ['%count%' => 1]],
            'one second under a day'         => [86399, 'remember_choice_duration_minutes', ['%count%' => 1439]],
            'forty-five minutes'             => [2700, 'remember_choice_duration_minutes', ['%count%' => 45]],
            'zero seconds'                   => [0, 'remember_choice_duration_minutes', ['%count%' => 0]],
        ];
    }
}
