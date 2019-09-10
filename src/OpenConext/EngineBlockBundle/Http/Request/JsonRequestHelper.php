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

namespace OpenConext\EngineBlockBundle\Http\Request;

use OpenConext\EngineBlockBundle\Http\Exception\BadApiRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class JsonRequestHelper
{
    private static $jsonErrors = [
        JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR      => 'Unexpected control character found',
        JSON_ERROR_SYNTAX         => 'Syntax error, malformed JSON',
        JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded'
    ];

    /**
     * @param Request $request
     * @return int|string|array
     * @throws BadRequestHttpException
     */
    public static function decodeContentOf(Request $request)
    {
        $stream = $request->getContent(true);
        $contents = stream_get_contents($stream);
        fclose($stream);

        $data     = json_decode($contents);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }

        $message       = 'Unable to parse JSON data';
        $lastErrorCode = json_last_error();
        if (isset(self::$jsonErrors[$lastErrorCode])) {
            $message .= ': ' . self::$jsonErrors[$lastErrorCode];
        }

        throw new BadApiRequestHttpException($message);
    }

    /**
     * @param Request $request
     * @return int|string|array
     * @throws BadRequestHttpException
     */
    public static function decodeContentAsArrayOf(Request $request)
    {
        $contents = $request->getContent();
        $data     = json_decode($contents, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }

        $message       = 'Unable to parse JSON data';
        $lastErrorCode = json_last_error();
        if (isset(self::$jsonErrors[$lastErrorCode])) {
            $message .= ': ' . self::$jsonErrors[$lastErrorCode];
        }

        throw new BadApiRequestHttpException($message);
    }
}
