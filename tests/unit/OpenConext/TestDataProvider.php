<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext;

use stdClass;

class TestDataProvider
{
    public static function notInteger()
    {
        return array_merge(
            self::emtpyString(),
            [
                'float'  => [1.234],
                'true'   => [true],
                'false'  => [false],
                'array'  => [[]],
                'object' => [new stdClass()],
                'null'   => [null],
                'string' => ['string']
            ]
        );
    }

    public static function notString()
    {
        return [
            'integer' => [1],
            'float'   => [1.234],
            'true'    => [true],
            'false'   => [false],
            'array'   => [[]],
            'object'  => [new stdClass()],
            'null'    => [null]
        ];
    }

    public static function notStringOrEmptyString()
    {
        return array_merge(
            self::notString(),
            self::emtpyString()
        );
    }

    public static function scalar()
    {
        return array_merge(
            self::nonStringScalar(),
            ['string' => ['some string']]
        );
    }

    public static function nonStringScalar()
    {
        return [
            'integer' => [1],
            'float'   => [1.234],
            'true'    => [true],
            'false'   => [false],
        ];
    }

    public static function nonStringScalarOrEmptyString()
    {
        return array_merge(
            self::nonStringScalar(),
            self::emtpyString()
        );
    }

    public static function nullOrScalar()
    {
        return array_merge(
            self::scalar(),
            ['null' => [null]]
        );
    }

    public static function notBoolean()
    {
        return array_merge(
            self::emtpyString(),
            [
                'integer' => [1],
                'float'   => [1.234],
                'array'   => [[]],
                'object'  => [new stdClass()],
                'null'    => [null],
                'string'  => ['string']
            ]
        );
    }

    public static function notArray()
    {
        return array_merge(
            self::emtpyString(),
            [
                'integer' => [1],
                'float'   => [1.234],
                'true'    => [true],
                'false'   => [false],
                'object'  => [new stdClass()],
                'null'    => [null],
                'string'  => ['string']
            ]
        );
    }

    public static function notNull()
    {
        return array_merge(
            self::emtpyString(),
            [
                'integer' => [1],
                'float'   => [1.234],
                'array'   => [[]],
                'true'    => [true],
                'false'   => [false],
                'object'  => [new stdClass()],
                'string'  => ['string']
            ]
        );
    }

    public static function notCallable()
    {
        return array_merge(
            self::emtpyString(),
            [
                'integer' => [1],
                'float'   => [1.234],
                'array'   => [[]],
                'true'    => [true],
                'false'   => [false],
                'object'  => [new stdClass()],
                'null'    => [null],
                'string'  => ['string']
            ]
        );
    }

    public static function emtpyString()
    {
        return [
            'empty string'    => [''],
            'new line only'   => ["\n"],
            'only whitespace' => ['   '],
            'nullbyte'        => [chr(0)],
        ];
    }

}
