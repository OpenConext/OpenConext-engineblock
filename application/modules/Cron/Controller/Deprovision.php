<?php

class Cron_Controller_Deprovision extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $this->previewOnly = (($this->_getRequest()->getQueryParameter('preview')) ? true : false);
        $deprovisionEngine = new EngineBlock_Deprovisioning();
        $this->deprovisionPreview = $deprovisionEngine->deprovision($this->previewOnly);
        $this->deprovisionConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->cron->deprovision;

        $this->_redirectToController("Index");
    }
}
