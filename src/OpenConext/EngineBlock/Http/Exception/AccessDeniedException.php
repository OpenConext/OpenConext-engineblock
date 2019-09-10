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

namespace OpenConext\EngineBlock\Http\Exception;

use Exception;

class AccessDeniedException extends HttpException
{
    public function __construct($resource, $code = 0, Exception $previous = null)
    {
        $message = sprintf('Access denied to resource "%s": are you properly authorized?', $resource);

        parent::__construct($message, $code, $previous);
    }
}
