<?php

namespace OpenConext\EngineBlock\Logger\Handler\FingersCrossed;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
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
     * @dataProvider configurationDataProvider
     *
     * @param array $config
     * @param string $expectedExceptionMessageContains
     */
    public function configuration_is_validated(array $config, $expectedExceptionMessageContains)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessageContains);

        ManualOrErrorLevelActivationStrategyFactory::createActivationStrategy($config);
    }

    public function configurationDataProvider()
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
