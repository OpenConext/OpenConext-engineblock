<?php

class EngineBlock_Test_Attributes_MessageRecorder extends Psr\Log\AbstractLogger
{
    /**
     * @var array
     */
    public $messages = array();

    public function log($level, $message, array $context = array())
    {
        $this->messages[] = array(
            'level'   => $level,
            'message' => $message,
            'context' => $context
        );
    }
}
