<?php
 
class Service_Controller_Rest extends EngineBlock_Controller_Abstract
{
    public function handleAction($actionName, $arguments)
    {
        return parent::handleAction($actionName, $arguments);
    }
    
    public function indexAction($url)
    {
    }
    
    public function metadataAction()
    {
        $this->setNoRender();

        $request = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest();
        $entityId = $request->getQueryParameter("entityid");
        $gadgetUrl = $request->getQueryParameter('gadgeturl');

        // If we were only handed a gadget url, no entity id, lookup the Service Provider entity id
        if ($gadgetUrl && !$entityId) {
            $identifiers = $this->_getRegistry()->findIdentifiersByMetadata('coin:gadgetbaseurl', $gadgetUrl);
            if (count($identifiers) > 1) {
                eblog()->warn(
                    "Multiple identifiers found for gadgetbaseurl: '$gadgetUrl'"
                );
                throw new EngineBlock_Exception('Multiple identifiers found for gadgetbaseurl');
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

        if (!$entityId) {
            throw new EngineBlock_Exception('No entity id provided to get metadata for?!');
        }

        if (isset($_REQUEST["keys"])) {
            $result = $this->_getRegistry()->getMetaDataForKeys($entityId, explode(",",$_REQUEST["keys"]));   
        } else {
            $result = $this->_getRegistry()->getMetadata($entityId);
        }

        header('Content-Type: application/json');
        echo json_encode($result);   
    }
    
    protected function _getRegistry()
    {
        return new EngineBlock_ServiceRegistry_CacheProxy();
    }   
}
