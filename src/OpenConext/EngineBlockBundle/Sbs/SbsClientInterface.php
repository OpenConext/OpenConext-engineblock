<?php

/**
 * Copyright 2025 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Sbs;

use OpenConext\EngineBlockBundle\Sbs\Dto\AttributesRequest;
use OpenConext\EngineBlockBundle\Sbs\Dto\AuthzRequest;

interface SbsClientInterface
{
    public const INTERRUPT = 'interrupt';
    public const AUTHORIZED = 'authorized';
    public const ERROR = 'error';

    public const VALID_MESSAGES = [self::INTERRUPT, self::AUTHORIZED, self::ERROR];

    public function authz(AuthzRequest $request) : AuthzResponse;

    public function requestAttributesFor(AttributesRequest $request) : AttributesResponse;

    public function getInterruptLocationLink(string $nonce);
}
