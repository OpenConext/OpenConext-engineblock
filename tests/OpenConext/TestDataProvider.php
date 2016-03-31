<?php

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
