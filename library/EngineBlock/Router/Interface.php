<?php

interface EngineBlock_Router_Interface
{
    public function route($uri);
    public function getControllerName();
    public function getModuleName();
    public function getActionName();
    public function getActionArguments();
}