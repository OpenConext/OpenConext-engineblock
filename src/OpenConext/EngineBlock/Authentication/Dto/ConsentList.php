<?php

/**
 * Copyright 2010 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Authentication\Dto;

final class ConsentList
{
    /**
     * @var Consent[]
     */
    private $consents = [];

    /**
     * @param Consent[] $consents
     */
    public function __construct(array $consents)
    {
        foreach ($consents as $consent) {
            $this->initialiseWith($consent);
        }
    }

    public function jsonSerialize()
    {
        return array_map(
            function (Consent $consent) {
                return $consent->jsonSerialize();
            },
            $this->consents
        );
    }

    private function initialiseWith(Consent $consent)
    {
        $this->consents[] = $consent;
    }
}
