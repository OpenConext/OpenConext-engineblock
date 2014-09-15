<?php

class Cron_Controller_Index extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $this->deprovisionConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->cron->deprovision;
    }
}
