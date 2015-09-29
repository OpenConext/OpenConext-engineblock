<?php

use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;

final class EngineBlock_Log_Monolog_Handler_FingersCrossed_ManualOrDecoratedActivationStrategy implements
    ActivationStrategyInterface
{
    /**
     * @var ActivationStrategyInterface
     */
    private $decoratedStrategy;

    /**
     * @var bool
     */
    private $wasManuallyActivated = false;

    public function __construct(ActivationStrategyInterface $decoratedStrategy)
    {
        $this->decoratedStrategy = $decoratedStrategy;
    }

    public function activate()
    {
        $this->wasManuallyActivated = true;
    }

    public function isHandlerActivated(array $record)
    {
        return $this->wasManuallyActivated || $this->decoratedStrategy->isHandlerActivated($record);
    }
}
