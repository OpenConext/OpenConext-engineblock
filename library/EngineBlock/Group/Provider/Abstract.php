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

class EngineBlock_Group_Exception_UserDoesNotExist extends EngineBlock_Exception
{
}

abstract class EngineBlock_Group_Provider_Abstract implements EngineBlock_Group_Provider_Interface
{
    protected $_stem = NULL;
    protected $_userId;

    protected $_preconditions = array();
    protected $_memberFilters = array();
    protected $_groupFilters  = array();

    public function addPrecondition($className, $options)
    {
        $this->_preconditions[] = array(
            'className' => $className,
            'options'   => $options
        );
        return $this;
    }

    public function configurePreconditions(Zend_Config $config)
    {
        if (isset($config->preconditions)) {
            foreach ($config->preconditions as $precondition) {
                $this->addPrecondition($precondition->className, $precondition);
            }
        }
    }

    public function configureGroupFilters(Zend_Config $config)
    {
        if (isset($config->groupFilters)) {
            foreach ($config->groupFilters as $groupFilterConfig) {
                $groupFilterClass = $groupFilterConfig->className;
                $filter = new $groupFilterClass($groupFilterConfig);
                $this->addGroupFilter($filter);
            }
        }
    }

    public function configureGroupMemberFilters(Zend_Config $config)
    {
        if (isset($config->groupMemberFilters)) {
            foreach ($config->groupMemberFilters as $groupMemberFilterConfig) {
                $groupMemberFilterClass = $groupMemberFilterConfig->className;
                $filter = new $groupMemberFilterClass($groupMemberFilterConfig);
                $this->addMemberFilter($filter);
            }
        }
    }

    public function getPreconditions()
    {
        return $this->_preconditions;
    }

    public function validatePreconditions()
    {
        $valid = true;
        foreach ($this->_preconditions as $precondition) {
            $className = $precondition['className'];
            $precondition = new $className($this, $precondition['options']);
            $valid = ($precondition->validate() AND $valid);
        }
        return $valid;
    }

    public function addGroupFilter(EngineBlock_Group_Provider_Filter_Interface $filter)
    {
        $this->_groupFilters[] = $filter;
        return $this;
    }

    public function getGroupFilters()
    {
        return $this->_groupFilters;
    }

    public function addMemberFilter(EngineBlock_Group_Provider_Filter_Interface $filter)
    {
        $this->_memberFilters[] = $filter;
        return $this;
    }

    public function getMemberFilters()
    {
        return $this->_memberFilters;
    }

    public function getUserId()
    {
        return $this->_userId;
    }

    public function setUserId($userId)
    {
        $this->_userId = $userId;
        return $this;
    }

    protected function _requireUserId()
    {
        if (!isset($this->_userId) || empty($this->_userId)) {
            throw new EngineBlock_Exception("No userid set for this provider, please set a userId with ->setUserId");
        }
    }
    
    /**
     * Some group provider implementations are able to host more than one set
     * of groups. In many implementations this is called a 'stem', and by
     * setting the stem we can choose which set of groups to use. While we use
     * the term 'stem' here, other implementations are free to implement the
     * filtering as they see fit. Implementations that don't support multiple
     * sets of groups, they can simply ignore this call 
     * @param String $stemIdentifier
     */
    public function setGroupStem($stemIdentifier)
    {
        $this->_stem = $stemIdentifier;
    }
    
    /**
     * Return the stem for this group provider. See setGroupStem for a more
     * elaborate explanation of stems.
     * @return String Stem Identifier
     */
    public function getGroupStem()
    {
        return $this->_stem;
    }
}
