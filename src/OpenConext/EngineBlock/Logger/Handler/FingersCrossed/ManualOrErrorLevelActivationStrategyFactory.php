<?php

namespace OpenConext\EngineBlock\Logger\Handler\FingersCrossed;

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use Psr\Log\LogLevel;

final class ManualOrErrorLevelActivationStrategyFactory implements ActivationStrategyFactory
{
    /**
     * @param array $config
     * @return ManualOrDecoratedActivationStrategy
     * @throws InvalidArgumentException
     */
    public static function createActivationStrategy(array $config)
    {
        $config = self::validateAndNormalizeConfig($config);

        return new ManualOrDecoratedActivationStrategy(
            new ErrorLevelActivationStrategy($config['action_level'])
        );
    }

    /**
     * @param array $config
     * @return array
     * @throws InvalidArgumentException
     */
    private static function validateAndNormalizeConfig(array $config)
    {
        Assertion::keyIsset($config, 'action_level', 'Missing configuration value, configuration key "%s" not found');
        Assertion::string($config['action_level']);

        $config['action_level'] = strtolower($config['action_level']);

        Assertion::choice(
            $config['action_level'],
            [
                LogLevel::EMERGENCY,
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::ERROR,
                LogLevel::WARNING,
                LogLevel::NOTICE,
                LogLevel::INFO,
                LogLevel::DEBUG,
            ],
            'Configured action level must be a valid PSR-compliant log level: "%s"'
        );

        return $config;
    }
}
