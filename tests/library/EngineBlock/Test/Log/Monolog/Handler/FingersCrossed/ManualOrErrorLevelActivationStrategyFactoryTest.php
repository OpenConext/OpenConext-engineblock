<?php

use EngineBlock_Log_Monolog_Handler_FingersCrossed_ManualOrErrorLevelActivationStrategyFactory
    as ManualOrErrorLevelActivationStrategyFactory;

final class EngineBlock_Test_Log_Monolog_Handler_FingersCrossed_ManualOrErrorLevelActivationStrategyFactoryTest
    extends PHPUnit_Framework_TestCase
{
    public function testItCreatesAManualOrDecoratedActivationStrategy()
    {
        ManualOrErrorLevelActivationStrategyFactory::factory(array('action_level' => 'INFO'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testItCannotBeCreatedTwice()
    {
        ManualOrErrorLevelActivationStrategyFactory::factory(array('action_level' => 'INFO'));
        ManualOrErrorLevelActivationStrategyFactory::factory(array('action_level' => 'INFO'));
    }

    public function configurationTests()
    {
        return array(
            'no action level' => array(
                array(),
                'expected configuration key "action_level" containing a string'
            ),
            'invalid action level' => array(
                array('action_level' => 'INVALID'),
                "'action_level' is not a valid log level string"
            ),
        );
    }

    /**
     * @dataProvider configurationTests
     * @param array  $config
     * @param string $expectedExceptionMessageContains
     */
    public function testItValidatesConfiguration(array $config, $expectedExceptionMessageContains)
    {
        $this->setExpectedException('EngineBlock_Log_InvalidConfigurationException', $expectedExceptionMessageContains);

        ManualOrErrorLevelActivationStrategyFactory::factory($config);
    }

    /** @var EngineBlock_Log_Monolog_Handler_FingersCrossed_ManualOrDecoratedActivationStrategy|null */
    private static $wasStrategy;

    /** @var ReflectionProperty */
    private static $strategyProperty;

    public static function setUpBeforeClass()
    {
        // The strategy has already been created during the bootstrapping of EngineBlock. Thus it needs to be removed
        // temporarily while we execute tests.
        $refl = new ReflectionClass(
            'EngineBlock_Log_Monolog_Handler_FingersCrossed_ManualOrErrorLevelActivationStrategyFactory'
        );
        self::$strategyProperty = $refl->getProperty('strategy');
        self::$strategyProperty->setAccessible(true);

        self::$wasStrategy = self::$strategyProperty->getValue();
        self::$strategyProperty->setValue(null);
    }

    public function setUp()
    {
        self::$strategyProperty->setValue(null);
    }

    public static function tearDownAfterClass()
    {
        self::$strategyProperty->setValue(self::$wasStrategy);
    }
}
