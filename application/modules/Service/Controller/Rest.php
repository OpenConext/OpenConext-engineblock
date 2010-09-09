<?php
 
class Service_Controller_Rest extends EngineBlock_Controller_Abstract
{
    public function handleAction($actionName, $arguments)
    {
        $this->setNoRender();
        return parent::handleAction($actionName, $arguments);
    }
    
    public function indexAction($url)
    {
    }
    
    public function metadataAction()
    {
        $entityId = $_REQUEST["entityid"];
        
        if (isset($_REQUEST["keys"])) {
            $result = $this->_getRegistry()->getMetaDataForKeys($entityId, explode(",",$_REQUEST["keys"]));   
        } else {
            $result = $this->_getRegistry()->getMetadata($entityId);
        }
       
        echo json_encode($result);
        
    }
    
    protected function _getRegistry()
    {
        return new EngineBlock_ServiceRegistry_Client();
    }
    
}
