<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

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
        $entityId  = $request->getQueryParameter("entityid");
        $gadgetUrl = $request->getQueryParameter('gadgeturl');

        // If we were only handed a gadget url, no entity id, lookup the Service Provider entity id
        if ($gadgetUrl && !$entityId) {
            $identifiers = $this->_getRegistry()->findIdentifiersByMetadata('coin:gadgetbaseurl', $gadgetUrl);
            if (count($identifiers) > 1) {
                EngineBlock_ApplicationSingleton::getLog()->warn(
                    "Multiple identifiers found for gadgetbaseurl: '$gadgetUrl'"
                );
                throw new EngineBlock_Exception(
                    'Multiple identifiers found for gadgetbaseurl',
                    EngineBlock_Exception::CODE_WARNING
                );
            }

            if (count($identifiers)===0) {
                EngineBlock_ApplicationSingleton::getInstance()->getLog()->notice(
                    "No Entity Id found for gadgetbaseurl '$gadgetUrl'"
                );
                $this->_getResponse()->setHeader('Content-Type', 'application/json');
                $this->_getResponse()->setBody(json_encode(new stdClass()));
                return;
            }

            $entityId = $identifiers[0];
        }

        if (!$entityId) {
            throw new EngineBlock_Exception(
                'No entity id provided to get metadata for?!',
                EngineBlock_Exception::CODE_NOTICE
            );
        }

        if (isset($_REQUEST["keys"])) {
            $result = $this->_getRegistry()->getMetaDataForKeys($entityId, explode(",",$_REQUEST["keys"]));   
        } else {
            $result = $this->_getRegistry()->getMetadata($entityId);
        }

        $result['entityId'] = $entityId;

        $this->_getResponse()->setHeader('Content-Type', 'application/json');
        $this->_getResponse()->setBody(json_encode($result));
    }

    /**
     * Get all ServiceProviders
     *
     * Optionally:
     * - Specify desired keys (defaults to 'all'): &keys=all or keys=name:en,name:nl
     * - Specify required metadata keys (defaults to no required fields),
     *   example: &required=name:nl,coin:oauth:secret
     */
    public function spAction()
    {
        $this->setNoRender();
        
        if (isset($_REQUEST["keys"])) {
            $serviceProviders = $this->_getRegistry()->getSpList(explode(",",$_REQUEST["keys"]));
        } else {
            $serviceProviders = $this->_getRegistry()->getSpList();
        }

        if (isset($_REQUEST['required'])) {
            $requiredKeys = explode(",",$_REQUEST["required"]);
            foreach ($serviceProviders as $entityId => $serviceProvider) {
                foreach ($requiredKeys as $requiredKey) {
                    if (!isset($serviceProvider[$requiredKey])) {
                        unset($serviceProviders[$entityId]);
                        break;
                    }
                }
            }
        }

        $this->_getResponse()->setHeader('Content-Type', 'application/json');
        $this->_getResponse()->setBody(json_encode($serviceProviders));
    }

    /**
     * @return EngineBlock_ServiceRegistry_Client
     */
    protected function _getRegistry()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryClient();
    }
}
