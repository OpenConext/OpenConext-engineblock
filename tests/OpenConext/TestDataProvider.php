<?php

namespace OpenConext;

use stdClass;

class TestDataProvider
{
    public static function notInteger()
    {
        return array_merge(
            self::emtpyString(),
            array(
                'float'  => array(1.234),
                'true'   => array(true),
                'false'  => array(false),
                'array'  => array(array()),
                'object' => array(new stdClass()),
                'null'   => array(null),
                'string' => array('string')
            )
        );
    }

    public static function notString()
    {
        return array(
            'integer' => array(1),
            'float'   => array(1.234),
            'true'    => array(true),
            'false'   => array(false),
            'array'   => array(array()),
            'object'  => array(new stdClass()),
            'null'    => array(null)
        );
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
            array('string'  => array('some string'))
        );
    }

    public static function nonStringScalar()
    {
        return array(
            'integer' => array(1),
            'float'   => array(1.234),
            'true'    => array(true),
            'false'   => array(false),
        );
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
            array('null' => array(null))
        );
    }

    public static function notBoolean()
    {
        return array_merge(
            self::emtpyString(),
            array(
                'integer' => array(1),
                'float'   => array(1.234),
                'array'   => array(array()),
                'object'  => array(new stdClass()),
                'null'    => array(null),
                'string'  => array('string')
            )
        );
    }

    public static function notArray()
    {
        return array_merge(
            self::emtpyString(),
            array(
                'integer' => array(1),
                'float'   => array(1.234),
                'true'    => array(true),
                'false'   => array(false),
                'object'  => array(new stdClass()),
                'null'    => array(null),
                'string'  => array('string')
            )
        );
    }

    public static function notCallable()
    {
        return array_merge(
            self::emtpyString(),
            array(
                'integer' => array(1),
                'float'   => array(1.234),
                'array'   => array(array()),
                'true'    => array(true),
                'false'   => array(false),
                'object'  => array(new stdClass()),
                'null'    => array(null),
                'string'  => array('string')
            )
        );
    }

    public static function emtpyString()
    {
        return array(
            'empty string'    => array(''),
            'new line only'   => array("\n"),
            'only whitespace' => array('   '),
            'nullbyte'        => array(chr(0)),
        );
    }
}
