<?php
 
class Authentication_Controller_Proxy extends EngineBlock_Controller_Abstract
{    
    /**
     * 
     *
     * @param string $encodedIdPEntityId
     * @return void
     */
    public function idPsMetaDataAction($encodedIdPEntityId = "")
    {
        // @todo Give the metaData for all known IdPs, where the SSO locations all go through EB
    }
}