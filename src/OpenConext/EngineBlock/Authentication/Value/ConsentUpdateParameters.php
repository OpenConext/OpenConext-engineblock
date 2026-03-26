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

namespace OpenConext\EngineBlock\Authentication\Value;

final class ConsentUpdateParameters
{
    public function __construct(
        public readonly string $attributeStableHash,
        /** @deprecated Remove after stable consent hash is running in production */
        public readonly string $attributeHash,
        public readonly string $hashedUserId,
        public readonly string $serviceId,
        public readonly string $consentType,
        /** @deprecated Remove after stable consent hash is running in production */
        public readonly bool $clearLegacyHash = false,
    ) {
    }
}
