<?php
/**
 * SURFconext Service Registry
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
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

ini_set('display_errors', true);
require '_includes.php';

$srConfig = SimpleSAML_Configuration::getConfig('module_serviceregistry.php');

$server = new EntityValidationServer();
$server->serve($_GET['eid']);

class EntityValidationServer
{
    protected $_response;

    /**
     * @var sspmod_serviceregistry_EntityController
     */
    protected $_entityController;

    public function __construct()
    {
        $this->_initializeResponse();
    }

    protected function _initializeResponse()
    {
        $response = new stdClass();
        $response->Validations = array();
        $response->Errors = array();
        $this->_response = $response;
    }

    public function serve($entityId)
    {
        if (!$this->_loadEntity($entityId)) {
            SimpleSAML_Logger::debug('No entity found!');
            return $this->_sendResponse();
        }

        $this->_checkMetadataValidity();
        return $this->_sendResponse();
    }

    protected function _loadEntity($entityId)
    {
        $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
        $entityController = new sspmod_serviceregistry_EntityController($janusConfig);
        $entityController->setEntity($entityId);
        $entityController->loadEntity();

        $this->_entityController = $entityController;

        return $entityController ? true : false;
    }

    protected function _checkMetadataValidity()
    {
        $validator = new Metadata_Validator($this->_entityController);
        $validator->validate();

        $this->_response->Errors = $validator->getErrors();
        $this->_response->Validations = $validator->getValidations();
    }

    protected function _sendResponse()
    {
        $this->_outputContentType('application/json');
        $this->_outputResponse();
    }

    protected function _outputContentType($contentType)
    {
        header("Content-Type: $contentType");
    }

    protected function _outputResponse()
    {
        //echo '<pre>'; var_dump($this->_response->Validations, true);
        echo json_encode($this->_response);
    }
}