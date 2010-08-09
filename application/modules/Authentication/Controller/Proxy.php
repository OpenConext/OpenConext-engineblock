<?php
 
class Authentication_Controller_Proxy extends EngineBlock_Controller_Abstract
{
    public function wayfAction()
    {
        $model = new Authentication_Model_WAYF();
        $this->model = $model;
    }

    public function consentAction()
    {
    }

    public function idpMetaDataAction()
    {
        // @todo Give the metaData for all known IdPs, where the SSO locations all go through EB
    }

    public function spMetaDataAction()
    {
        // @todo Maybe?
    }
}
