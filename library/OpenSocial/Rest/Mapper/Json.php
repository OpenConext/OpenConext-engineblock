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

/**
 * Map an OpenSocial JSON response to a model.
 *
 * @throws OpenSocial_Rest_Exception
 */
class OpenSocial_Rest_Mapper_Json implements OpenSocial_Rest_Mapper_Interface
{
    /**
     * @var string
     */
    private $_modelClassName;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->_modelClassName = $className;
    }

    /**
     * Map a JSON OpenSocial response to a model.
     *
     * @throws OpenSocial_Rest_Exception
     * @param string $responseBody
     * @return array Models (array of OpenSocial_Model_Interface)
     */
    public function map($responseBody)
    {
        $data = json_decode($responseBody);

        if (!isset($data->entry)) {
            throw new OpenSocial_Rest_Exception("No entry / entries found in response?");
        }

        if (!is_array($data->entry)) { // Single entry
            return array($this->_mapEntryToModel($data->entry));
        }
        else { // Multiple entries
            $groups = array();
            foreach ($data->entry as $entry) {
                $groups[] = $this->_mapEntryToModel($entry);
            }
            return $groups;
        }
    }

    /**
     * Map a single entity to a new model.
     *
     * @param stdClass $entry
     * @return OpenSocial_Model_Interface
     */
    protected function _mapEntryToModel(stdClass $entry)
    {
        return $this->_copyEntryPropertiesToModel($entry, new $this->_modelClassName);
    }

    /**
     * Simple copying of properties
     *
     * @param stdClass $entry
     * @param OpenSocial_Model_Interface $model
     * @return OpenSocial_Model_Interface
     */
    protected function _copyEntryPropertiesToModel(stdClass $entry, OpenSocial_Model_Interface $model)
    {
        foreach ($entry as $key => $value) {
            if (property_exists($model, $key)) {
                $model->$key = $value;
            }
        }
        return $model;
    }
}