<?php
 
class Authentication_Controller_Proxy extends EngineBlock_Controller_Abstract
{    
    /**
     * 
     *
     * @param string $encodedIdPEntityId
     * @return void
     */
    public function idPsMetaDataAction()
    {
        $this->setNoRender();

        // @todo Give the metaData for all known IdPs, where the SSO locations all go through EB
        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->idPsMetadata(EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryString());
    }
}