<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Exception;

use Exception;
use Throwable;

/**
 * Art (Alberts-Renterghem-Tuck)
 *
 * A numeric error code that is calculated over the exception message, that is
 * included on the error page. The art-code can assist the support desk in
 * recognizing errors (based on the art-code). When calculating the art-code,
 * text between quotes (single and double) are stripped out. Assuming text
 * between quotes is variable text for example, containing the name of an SP.
 *
 * Example:
 *
 * Exception with message:
 *  Unable to verify the Foo of "Bar"
 *
 * Is transformed to:
 *  Unable to verify the Foo of
 *
 * And the art-code is calculated over that string, resulting in something like:
 * 32193
 */
class Art
{
    /**
     * @param Exception $exception
     * @return string
     */
    public static function forException(Throwable $exception)
    {
        return self::calculateArt(get_class($exception), $exception->getMessage());
    }

    /**
     * @param string $className
     * @param string $message
     * @return string
     */
    private static function calculateArt($className, $message)
    {
        $message = self::stripVariableArgumentsFromMessage($message);

        return substr(abs(crc32(md5($className . $message))), 0, 5);
    }

    /**
     * Strip variable arguments from exception messages.
     *
     * Some exception messages are formatted using sprintf, and result in a
     * unique art-code for each distinct message. In order for the art-code to
     * be useful it must be the same for each distinct error situation without
     * taking into account variable parts of the message.
     *
     * This method strips all strings inside quotes. This is not perfect
     * because it relies on sprintf arguments to always be quoted inside the
     * message.
     *
     * @param $message
     * @return string
     */
    private static function stripVariableArgumentsFromMessage($message)
    {
        return preg_replace('#".*"|\'.*\'#', '', $message);
    }
}
