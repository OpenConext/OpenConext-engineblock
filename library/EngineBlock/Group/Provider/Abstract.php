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

/**
 * Base class with default functionality for Group Providers.
 */
abstract class EngineBlock_Group_Provider_Abstract implements EngineBlock_Group_Provider_Interface
{
    protected $_id;
    protected $_name;
    protected $_userId;
    protected $_stem = NULL;

    protected $_preconditions = array();
    protected $_memberFilters = array();
    protected $_groupFilters  = array();

    public function getId()
    {
        return $this->_id;
    }

    public function getDisplayName()
    {
        return $this->_name;
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
     * @return EngineBlock_Group_Provider_Interface
     */
    public function setGroupStem($stemIdentifier)
    {
        $this->_stem = $stemIdentifier;
        return $this;
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

    protected function _getStemmedGroupId($groupIdentifier)
    {
        if (isset($this->_stem) && !empty($this->_stem)) {
            return $this->_stem . ':' . $groupIdentifier;
        }
        return $groupIdentifier;
    }

    public function addPrecondition($className, $options = null)
    {
        $this->_preconditions[] = array(
            'className' => $className,
            'options'   => $options
        );
        
        return $this;
    }

    public function removePreconditionByClassName($className)
    {
        foreach ($this->_preconditions as $key => $precondition) {
            if ($precondition['className'] === $className) {
                unset($this->_preconditions[$key]);
            }
        }

        return $this;
    }

    public function configurePreconditions(Zend_Config $config)
    {
        if (isset($config->preconditions)) {
            foreach ($config->preconditions as $precondition) {
                $this->addPrecondition($precondition->className, $precondition);
            }
        }
        
        return $this;
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
            if (!class_exists($className, true)) {
                ebLog()->warn("Classname '$className' not found for precondition! Skipping precondition!");
                continue;
            }
            $precondition = new $className($this, $precondition['options']);
            $preconditionValid = $precondition->validate();
            $valid = ($preconditionValid AND $valid);
        }
        return $valid;
    }

    /**
     * Is this GroupProvider able to return details for the given userId based on the configured memberFilter
     * @abstract
     * @return boolean true is the userId is a partial matched with this GroupProviders urn
     */
    public function isGroupProviderForUser()
    {
        return $this->validatePreconditions();
    }


    public function configureGroupFilters(Zend_Config $config)
    {
        if (isset($config->groupFilters)) {
            foreach ($config->groupFilters as $groupFilterConfig) {
                $groupFilterClass = $groupFilterConfig->className;
                if (!class_exists($groupFilterClass, true)) {
                    ebLog()->warn("Classname '$groupFilterClass' not found for group filter! Skipping group filter!");
                    continue;
                }
                $filter = new $groupFilterClass($groupFilterConfig);
                $this->addGroupFilter($filter);
            }
        }
        return $this;
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

    public function configureGroupMemberFilters(Zend_Config $config)
    {
        if (isset($config->groupMemberFilters)) {
            foreach ($config->groupMemberFilters as $groupMemberFilterConfig) {
                $groupMemberFilterClass = $groupMemberFilterConfig->className;
                if (!class_exists($groupMemberFilterClass, true)) {
                    ebLog()->warn("Classname '$groupMemberFilterClass' not found for group member filter! Skipping group member filter!");
                    continue;
                }
                $filter = new $groupMemberFilterClass($groupMemberFilterConfig);
                $this->addMemberFilter($filter);
            }
        }
        return $this;
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

    /**
     * Check if there are any decorators specified in the configuration,
     * if so, apply them and return the decorated provider.
     *
     * @return EngineBlock_Group_Provider_Interface
     */
    public function configureDecoratorChain(Zend_Config $config)
    {
        if (!isset($config->decorators) || empty($config->decorators)) {
            return $this;
        }

        $decoratedProvider = $this;
        foreach ($config->decorators as $decoratorConfig) {
            $decoratorClassName = $decoratorConfig->className;
            if (!class_exists($decoratorClassName, true)) {
                ebLog()->warn("Classname '$decoratorClassName' not found for decorator! Skipping decorator!");
                continue;
            }

            $decoratedProvider = $decoratorClassName::createFromConfigsWithProvider(
                $decoratedProvider,
                $decoratorConfig
            );
        }
        return $decoratedProvider;
    }
}
