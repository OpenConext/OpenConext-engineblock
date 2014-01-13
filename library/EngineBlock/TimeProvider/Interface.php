<?php

interface EngineBlock_TimeProvider_Interface
{
    public function timestamp($deltaSeconds = 0, $time = null);

    public function time();
}
