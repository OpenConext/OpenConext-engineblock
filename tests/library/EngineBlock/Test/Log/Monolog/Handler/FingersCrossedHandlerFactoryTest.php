<?php

use EngineBlock_Log_Monolog_Handler_FingersCrossedHandlerFactory as FingersCrossedHandlerFactory;
use EngineBlock_Test_Log_Monolog_Handler_FingersCrossed_ActivationStrategyStubFactory as ActivationStrategyStubFactory;

final class EngineBlock_Test_Log_Monolog_Handler_FingersCrossedHandlerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        ActivationStrategyStubFactory::$stubToReturn = null;
        ActivationStrategyStubFactory::$expectedConfiguration = null;
    }

    public function testItConfiguresTheHandlerAndActivationStrategy()
    {
        $config = array(
            'handler' => 'babelfish',
            'activation_strategy' => array(
                'factory' => 'EngineBlock_Test_Log_Monolog_Handler_FingersCrossed_ActivationStrategyStubFactory',
                'conf' => array("Don't panic"),
            ),
        );

        $decoratedHandler = $this->getMockBuilder('Monolog\Handler\HandlerInterface')->getMock();
        $decoratedHandler->expects($this->once())->method('handleBatch');

        $activationStrategy =
            $this->getMockBuilder('Monolog\Handler\FingersCrossed\ActivationStrategyInterface')->getMock();
        $activationStrategy->expects($this->once())->method('isHandlerActivated')->willReturn(true);
        ActivationStrategyStubFactory::$stubToReturn = $activationStrategy;
        ActivationStrategyStubFactory::$expectedConfiguration = array("Don't panic");

        $handler = FingersCrossedHandlerFactory::factory($config, array('babelfish' => $decoratedHandler), false);
        $handler->handle(array());

        $this->assertInstanceOf('Monolog\Handler\FingersCrossedHandler', $handler);
    }

    public function testItConfiguresADefaultPassthruLevelOfNullMeaningNoMessagesAreFlushedOnClose()
    {
        $config = array(
            'handler' => 'babelfish',
            'activation_strategy' => array(
                'factory' => 'EngineBlock_Test_Log_Monolog_Handler_FingersCrossed_ActivationStrategyStubFactory',
            ),
        );

        $decoratedHandler = $this->getMockBuilder('Monolog\Handler\HandlerInterface')->getMock();
        $decoratedHandler->expects($this->never())->method('handleBatch');

        $activationStrategy =
            $this->getMockBuilder('Monolog\Handler\FingersCrossed\ActivationStrategyInterface')->getMock();
        $activationStrategy->expects($this->any())->method('isHandlerActivated')->willReturn(false);
        ActivationStrategyStubFactory::$stubToReturn = $activationStrategy;

        $handler = FingersCrossedHandlerFactory::factory($config, array('babelfish' => $decoratedHandler), false);
        $handler->handle(array());
        $handler->close();

        $this->assertInstanceOf('Monolog\Handler\FingersCrossedHandler', $handler);
    }

    public function testItConfiguresAPassthruLevel()
    {
        $config = array(
            'handler' => 'babelfish',
            'activation_strategy' => array(
                'factory' => 'EngineBlock_Test_Log_Monolog_Handler_FingersCrossed_ActivationStrategyStubFactory',
            ),
            'passthru_level' => 'INFO'
        );

        $decoratedHandler = $this->getMockBuilder('Monolog\Handler\HandlerInterface')->getMock();
        $decoratedHandler->expects($this->once())
            ->method('handleBatch')
            ->with(array(1 => array('level' => \Monolog\Logger::INFO)));

        $activationStrategy =
            $this->getMockBuilder('Monolog\Handler\FingersCrossed\ActivationStrategyInterface')->getMock();
        $activationStrategy->expects($this->any())->method('isHandlerActivated')->willReturn(false);
        ActivationStrategyStubFactory::$stubToReturn = $activationStrategy;

        $handler = FingersCrossedHandlerFactory::factory($config, array('babelfish' => $decoratedHandler), false);
        $handler->handle(array('level' => \Monolog\Logger::DEBUG));
        $handler->handle(array('level' => \Monolog\Logger::INFO));
        $handler->close();

        $this->assertInstanceOf('Monolog\Handler\FingersCrossedHandler', $handler);
    }

    public function configurationTests()
    {
        return array(
            'no handler' => array(
                array(),
                'expected configuration key "handler" containing a string'
            ),
            'invalid passthru level' => array(
                array('handler' => 'babelfish', 'passthru_level' => 'invalid'),
                "'passthru_level' is not a valid log level string"
            ),
            'missing activation strategy' => array(
                array('handler' => 'babelfish', 'passthru_level' => 'debug'),
                'expected configuration key "activation_strategy.factory" containing a string'
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

        FingersCrossedHandlerFactory::factory($config, array(), false);
    }

    public function testItReturnsTheDecoratedHandlerWhenDebugging()
    {
        $config = array(
            'handler' => 'babelfish',
            'activation_strategy' => array(
                'factory' => 'EngineBlock_Test_Log_Monolog_Handler_FingersCrossed_ActivationStrategyStubFactory',
            ),
        );

        $decoratedHandler = $this->getMockBuilder('Monolog\Handler\HandlerInterface')->getMock();

        $handler = FingersCrossedHandlerFactory::factory($config, array('babelfish' => $decoratedHandler), true);

        $this->assertSame($decoratedHandler, $handler);
    }
}
