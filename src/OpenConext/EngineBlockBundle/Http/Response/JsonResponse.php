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

namespace OpenConext\EngineBlockBundle\Http\Response;

use OpenConext\EngineBlockBundle\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

final class JsonResponse extends Response
{
    public function __construct($content, $status = 200, array $headers = [])
    {
        if (!is_string($content)) {
            throw InvalidArgumentException::invalidType('string', 'content', $content);
        }

        parent::__construct($content, $status, $headers);

        $this->headers->set('Content-Type', 'application/json');
    }
}
