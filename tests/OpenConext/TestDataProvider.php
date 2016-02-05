<?php

namespace OpenConext;

use stdClass;

class TestDataProvider
{
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
            array(
                'empty string'    => array(''),
                'new line only'   => array("\n"),
                'only whitespace' => array('   '),
                'nullbyte'        => array(chr(0)),
            )
        );
    }

    public static function scalar()
    {
        return array(
            'integer' => array(1),
            'float'   => array(1.234),
            'true'    => array(true),
            'false'   => array(false),
            'string'  => array('some string'),
        );
    }

    public static function nullOrScalar()
    {
        return array_merge(
            self::scalar(),
            array('null' => array(null))
        );
    }
}
