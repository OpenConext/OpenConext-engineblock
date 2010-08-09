<?php
 
class EngineBlock_MetaData 
{
    protected $_singleSignOnUrlByEntityId = array();

    public function setSingleSignOnUrlByEntityId($entityId, $url)
    {
        $this->_singleSignOnUrlByEntityId[$entityId] = $url;
        return $this;
    }

    public function getSingleSignOnUrlByEntityId($entityId)
    {
        return $this->_singleSignOnUrlByEntityId[$entityId];
    }
}
