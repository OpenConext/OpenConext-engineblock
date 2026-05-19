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

namespace OpenConext\EngineBlockBundle\Controller;

use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

class ActuatorController extends AbstractController
{
    private const string PUBLIC_DIR = "public";

    private const string VERSION_FILE = "version.json";

    private const string BUILD_TIME_FORMAT = "Y-m-d\TH:i:s.v\Z";

    private LoggerInterface $logger;

    private string $projectDir;

    public function __construct(
        LoggerInterface $logger,
        string          $projectDir
    )
    {
        $this->logger = $logger;
        $this->projectDir = $projectDir;
    }

    #[Route(
        path: '/actuator/info',
        name: 'actuator_info',
        methods: ['GET']
    )]
    public function getBuildInfo(): JsonResponse
    {
        try {
            $versionFile = $this->projectDir . DIRECTORY_SEPARATOR . self::PUBLIC_DIR .
                DIRECTORY_SEPARATOR . self::VERSION_FILE;

            $versionContent = json_decode(file_get_contents($versionFile), true);

            // Calculate days since release
            $buildTimeString = $versionContent["time"];
            $buildTime = DateTime::createFromFormat(self::BUILD_TIME_FORMAT, $buildTimeString);
            $daysSinceRelease = (new DateTime())->diff($buildTime)->days;

            $buildInfo = [
                'build' => $versionContent ?? [],
                'days_since_release' => $daysSinceRelease
            ];
        } catch (Throwable $e) {
            $this->logger->error("Failed to build actuator info: " . $e->getMessage(), $e->getTrace());
            $buildInfo = [];
        }

        return $this->json($buildInfo);
    }
}
