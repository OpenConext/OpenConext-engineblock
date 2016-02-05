<?php

use OpenConext\EngineBlock\Logger\Handler\FingersCrossed\ManualOrErrorLevelActivationStrategyFactory;
use PHPUnit_Framework_TestCase as TestCase;

class ManualOrErrorLevelActivationStrategyFactoryTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function factory_creates_a_manual_or_decorated_activation_strategy()
    {
        ManualOrErrorLevelActivationStrategyFactory::createActivationStrategy(array('action_level' => 'INFO'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Logger
     *
     * @dataProvider configurationTests
     * @param array $config
     * @param string $expectedExceptionMessageContains
     */
    public function testItValidatesConfiguration(array $config, $expectedExceptionMessageContains)
    {
        $this->setExpectedException('OpenConext\EngineBlock\Exception\InvalidArgumentException', $expectedExceptionMessageContains);
        ManualOrErrorLevelActivationStrategyFactory::createActivationStrategy($config);
    }

    public function configurationTests()
    {
        return array(
            'no action level'      => array(
                array(),
                'Missing configuration value'
            ),
            'invalid action level' => array(
                array('action_level' => 'INVALID'),
                'Configured action level must be a valid PSR-compliant log level'
            ),
        );
    }
}
