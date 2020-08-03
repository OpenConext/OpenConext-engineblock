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

namespace OpenConext\EngineBlock\Metadata;

use OpenConext\EngineBlock\Assert\Assertion;

class MfaEntityFactory
{
    private const TRANSPARENT_AUTHN_CONTEXT = 'transparant_authn_context';

    public static function from(string $entityId, string $level)
    {
        if ($level === self::TRANSPARENT_AUTHN_CONTEXT) {
            return new TransparentMfaEntity($entityId, $level);
        }

        return new MfaEntity($entityId, $level);
    }

    public static function fromJson($mfaEntityData)
    {
        Assertion::keyExists($mfaEntityData, 'entityId', 'MFA entityId must be specified');
        Assertion::keyExists($mfaEntityData, 'level', 'MFA entity level must be specified');
        Assertion::string($mfaEntityData['entityId'], 'MFA entityId must be of type string');
        Assertion::string($mfaEntityData['level'], 'MFA level must be of type string');

        if ($mfaEntityData['level'] === self::TRANSPARENT_AUTHN_CONTEXT) {
            return new TransparentMfaEntity($mfaEntityData['entityId'], $mfaEntityData['level']);
        }

        return new MfaEntity($mfaEntityData['entityId'], $mfaEntityData['level']);
    }
}
