<?php
 
class Authentication_Controller_IdentityProvider extends EngineBlock_Controller_Abstract
{
    public function singleSignOnAction($idPEntityId = null)
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->singleSignOn($idPEntityId);
    }

    public function metadataAction()
    {
        // @todo Give the metadata (with single sign on URL) for EngineBlock
    }
}