<?php

class EngineBlock_DateTime implements EngineBlock_DateTimeProviderInterface
{
    public function time()
    {
        return time();
    }

    public function date($format, $timestamp = null)
    {
        return date($format, $timestamp);
    }
}
