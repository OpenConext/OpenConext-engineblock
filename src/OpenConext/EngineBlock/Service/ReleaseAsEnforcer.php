<?php

/**
 * Copyright 2024 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Service;

use Psr\Log\LoggerInterface;

class ReleaseAsEnforcer implements ReleaseAsEnforcerInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function enforce(array $attributes, array $releaseAsOverrides)
    {
        foreach ($releaseAsOverrides as $oldAttributeName => $overrideValue) {
            $newAttributeName = $overrideValue[0]['release_as'];
            if (!array_key_exists($oldAttributeName, $attributes)) {
                $this->logger->notice(
                    sprintf(
                        'Releasing "%s" as "%s" is not possible, "%s" is not in assertion',
                        $oldAttributeName,
                        $newAttributeName,
                        $oldAttributeName
                    )
                );
                continue;
            }
            if (is_null($attributes[$oldAttributeName])) {
                $this->logger->warning(
                    sprintf(
                        'Releasing "%s" as "%s" is not possible, value for "%s" is null',
                        $oldAttributeName,
                        $newAttributeName,
                        $oldAttributeName
                    )
                );
                unset($attributes[$oldAttributeName]);
                continue;
            }
            $attributeValue = $attributes[$oldAttributeName];
            unset($attributes[$oldAttributeName]);
            $this->logger->notice(
                sprintf(
                    'Releasing attribute "%s" as "%s" as specified in the release_as ARP setting',
                    $oldAttributeName,
                    $newAttributeName
                )
            );
            $attributes[$newAttributeName] = $attributeValue;
        }
        return $attributes;
    }
}
