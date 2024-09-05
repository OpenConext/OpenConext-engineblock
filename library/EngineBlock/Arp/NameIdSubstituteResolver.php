<?php

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use Psr\Log\LoggerInterface;

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

class EngineBlock_Arp_NameIdSubstituteResolver
{
    /**
     * @var LoggerInterface 
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function findNameIdSubstitute(AttributeReleasePolicy $arp, array $responseAttributes): ?string
    {
        $substituteAttribute = $arp->findNameIdSubstitute();
        if ($substituteAttribute !== null && array_key_exists($substituteAttribute, $responseAttributes)) {
            $this->logger->notice(
                sprintf(
                    'Found a NameId substitute ("use_as_nameid", %s will be used as NameID)',
                    $substituteAttribute
                )
            );
            return reset($responseAttributes[$substituteAttribute]);
        }
        return null;
    }
}
