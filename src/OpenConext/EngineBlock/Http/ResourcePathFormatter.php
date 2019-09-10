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


namespace OpenConext\EngineBlock\Http;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\RuntimeException;

final class ResourcePathFormatter
{
    /**
     * @param string $resourcePathFormat
     * @param array $parameters
     * @return string
     * @throws RuntimeException
     */
    public static function format($resourcePathFormat, array $parameters)
    {
        Assertion::string($resourcePathFormat, 'Resource path format "%s" expected to be string, type %s given');

        if (count($parameters) > 0) {
            $resource = vsprintf($resourcePathFormat, array_map('urlencode', $parameters));
        } else {
            $resource = $resourcePathFormat;
        }

        if (empty($resource)) {
            throw new RuntimeException(sprintf(
                'Could not construct resource path from format "%s", parameters "%s"',
                $resourcePathFormat,
                implode('","', $parameters)
            ));
        }

        return $resource;
    }
}
