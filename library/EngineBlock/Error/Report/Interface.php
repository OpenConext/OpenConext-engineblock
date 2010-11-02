<?php

interface EngineBlock_Error_Report_Interface
{
    public function __construct($config);
    public function report(Exception $exception);
}
