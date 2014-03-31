<?php

class EngineBlock_TimeProvider_Default implements EngineBlock_TimeProvider_Interface
{
    const TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s\Z';

    public function timestamp($deltaSeconds = 0, $time = null)
    {
        if (is_null($time)) {
            $time = time();
        }
        return gmdate(self::TIMESTAMP_FORMAT, $time + $deltaSeconds);
    }

    public function time()
    {
        return time();
    }
}
