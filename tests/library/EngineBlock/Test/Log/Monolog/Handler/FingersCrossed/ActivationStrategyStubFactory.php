<?php

final class EngineBlock_Test_Log_Monolog_Handler_FingersCrossed_ActivationStrategyStubFactory implements
    EngineBlock_Log_Monolog_Handler_FingersCrossed_ActivationStrategyFactory
{
    public static $stubToReturn;
    public static $expectedConfiguration;

    public static function factory(array $config)
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
