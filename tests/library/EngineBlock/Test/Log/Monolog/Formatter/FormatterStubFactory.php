<?php

final class EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory extends PHPUnit_Framework_TestCase
{
    public static $stubToReturn;
    public static $expectedConfiguration;

    public static function factory($config)
    {
        if (self::$expectedConfiguration !== null) {
            PHPUnit_Framework_TestCase::assertEquals(
                self::$expectedConfiguration,
                $config,
                'Expected formatter configuration did not match actual configuration'
            );
        }

        return self::$stubToReturn;
    }
}
