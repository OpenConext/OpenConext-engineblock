<?php

interface EngineBlock_DateTimeProviderInterface
{
    public function date($format, $timestamp = null);
    public function time();
}
