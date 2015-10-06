<?php

final class EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory implements
    EngineBlock_Log_Monolog_Formatter_FormatterFactory
{
    public static $stubToReturn;
    public static $expectedConfiguration;

    public static function factory(array $config)
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
