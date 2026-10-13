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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use OpenConext\EngineBlockFunctionalTestingBundle\Saml2\Recording\SamlTrafficRecorder;

class SamlRecordingContext implements Context
{
    // The application runs in Apache, so the recorder is told what to do through a marker file, not an env var.
    /**
     * @BeforeScenario
     */
    public function markCurrentScenario(BeforeScenarioScope $scope): void
    {
        $directory = getenv(SamlTrafficRecorder::DIRECTORY_ENV);
        if (!is_string($directory) || $directory === '') {
            self::clearMarker();
            return;
        }

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $scenario = sprintf(
            '%s_L%d',
            basename($scope->getFeature()->getFile() ?? 'unknown', '.feature'),
            $scope->getScenario()->getLine()
        );

        file_put_contents(
            SamlTrafficRecorder::MARKER_FILE,
            json_encode(['directory' => $directory, 'scenario' => $scenario], JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @AfterSuite
     */
    public static function stopRecording(): void
    {
        self::clearMarker();
    }

    private static function clearMarker(): void
    {
        if (is_file(SamlTrafficRecorder::MARKER_FILE)) {
            unlink(SamlTrafficRecorder::MARKER_FILE);
        }
    }
}
