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
        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $entityId = $request->getQueryParameter("entityid");
        $gadgetUrl = $request->getQueryParameter('gadgeturl');

        if (!$entityId && $gadgetUrl) {
            $identifiers = $this->_getRegistry()->findIdentifiersByMetadata('coin:gadgetbaseurl', $gadgetUrl);
            if (count($identifiers) > 1) {
                throw new EngineBlock_Exception('Multiple identifiers found for gadgetbaseurl');
                EngineBlock_ApplicationSingleton::getInstance()->getLog()->warn(
                    "Multiple identifiers found for gadgetbaseurl: '$gadgetUrl'"
                );
                echo json_encode(new stdClass());
                return;
            }

            if (count($identifiers)===0) {
                EngineBlock_ApplicationSingleton::getInstance()->getLog()->warn(
                    "No Entity Id found for gadgetbaseurl '$gadgetUrl'"
                );
                echo json_encode(new stdClass());
                return;
            }

            $entityId = $identifiers[0];
        }
        else if (!$entityId) {
            throw new EngineBlock_Exception('No entity id provided to get metadata for?!');
        }

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
