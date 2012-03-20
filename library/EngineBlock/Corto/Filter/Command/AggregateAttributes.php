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
 * Enrich the current set of attributes with attributes from other attribute providers.
 */
class EngineBlock_Corto_Filter_Command_AggregateAttributes extends EngineBlock_Corto_Filter_Command_Abstract
{
    const URN_OID_COLLAB_PERSON_ID  = 'urn:oid:1.3.6.1.4.1.1076.20.40.40.1';
    const VO_NAME_ATTRIBUTE         = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2';

    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        $voContext = null;
        if (isset($attributes[self::VO_NAME_ATTRIBUTE])) {
            $voContext = $this->_responseAttributes[self::VO_NAME_ATTRIBUTE][0];
        }

        $aggregator = $this->_getAttributeAggregator(
            $this->_getAttributeProviders($this->_spMetadata['EntityId'], $voContext)
        );
        $this->_responseAttributes = $aggregator->aggregateFor(
            $this->_responseAttributes,
            $this->_responseAttributes[self::URN_OID_COLLAB_PERSON_ID][0]
        );
    }

    protected function _getAttributeAggregator($providers)
    {
        return new EngineBlock_AttributeAggregator($providers);
    }

    protected function _getAttributeProviders($spEntityId, $voContext = null)
    {
        $providers = array();
        if ($voContext) {
            $providers[] = new EngineBlock_AttributeProvider_VoManage($voContext, $spEntityId);
        }
        return $providers;
    }
}