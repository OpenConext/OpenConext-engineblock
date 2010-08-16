<?php
 
class EngineBlock_ServiceRegistryMock 
{
    protected $_idpList;
    protected $_spList;

    public function setIdPList($idPs)
    {
        $this->_idpList = $idPs;
        return $this;
    }

    public function setSpList($sps)
    {
        $this->_spList = $sps;
    }

    public function getIdPList()
    {
        return $this->_idpList;
    }

    public function getSpList()
    {
        return $this->_spList;
    }
}
