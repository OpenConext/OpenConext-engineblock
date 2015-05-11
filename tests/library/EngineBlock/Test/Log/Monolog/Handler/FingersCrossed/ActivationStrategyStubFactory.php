<?php

final class EngineBlock_Test_Log_Monolog_Handler_FingersCrossed_ActivationStrategyStubFactory
{
    public static $stubToReturn;
    public static $expectedConfiguration;

    public static function factory($config)
    {
        if (self::$expectedConfiguration !== null) {
            PHPUnit_Framework_TestCase::assertEquals(
                self::$expectedConfiguration,
                $config,
                'Expected activation strategy configuration did not match actual configuration'
            );
        }

        return self::$stubToReturn;
    }
}
