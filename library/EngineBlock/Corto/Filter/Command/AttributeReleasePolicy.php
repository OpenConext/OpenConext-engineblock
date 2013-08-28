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

class EngineBlock_Corto_Filter_Command_AttributeReleasePolicy extends EngineBlock_Corto_Filter_Command_Abstract
{
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
        $spEntityId = $this->_spMetadata['EntityId'];

        $serviceRegistryAdapter = $this->_getServiceRegistryAdapter();
        $arp = $serviceRegistryAdapter->getArp($spEntityId);
        if ($arp) {
            EngineBlock_ApplicationSingleton::getLog()->info(
                "Applying attribute release policy {$arp['name']} for $spEntityId"
            );

            $newAttributes = array();
            foreach ($this->_responseAttributes as $attribute => $attributeValues) {
                if (!isset($arp['attributes'][$attribute])) {
                    EngineBlock_ApplicationSingleton::getLog()->info(
                        "ARP: Removing attribute $attribute"
                    );
                    continue;
                }

                $allowedValues = $arp['attributes'][$attribute];
                if (in_array('*', $allowedValues)) {
                    // Passthrough all values
                    $newAttributes[$attribute] = $attributeValues;
                    continue;
                }

                foreach ($attributeValues as $attributeValue) {
                    if (in_array($attributeValue, $allowedValues)) {
                        if (!isset($newAttributes[$attribute])) {
                            $newAttributes[$attribute] = array();
                        }
                        $newAttributes[$attribute][] = $attributeValue;
                    }
                }
            }
            $this->_responseAttributes = $newAttributes;
        }
    }

    protected function _getServiceRegistryAdapter()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryAdapter();
    }
}