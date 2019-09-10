<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;

abstract class EngineBlock_Attributes_Validator_Abstract implements EngineBlock_Attributes_Validator_Interface
{
    /**
     * @var string
     */
    protected $_attributeName;

    /**
     * @var string
     */
    protected $_attributeAlias;

    /**
     * @var mixed
     */
    protected $_options;

    /**
     * @var array
     */
    protected $_messages = array();

    public function __construct($attributeName, $options)
    {
        $this->_attributeName = $attributeName;
        $this->_options = $options;
    }

    public function setAttributeAlias($aliasName)
    {
        $this->_attributeAlias = $aliasName;
        return $this;
    }

    public function getMessages()
    {
        return $this->_messages;
    }
}
