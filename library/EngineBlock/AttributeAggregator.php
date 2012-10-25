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
 * Aggregate attributes from multiple sources.
 *
 * @todo Should be done asynchronously, see also:
 * https://wiki.surfnetlabs.nl/confluence/display/coindo2010/Asynchronous+attribute+aggregation+from+Attribute+Providers
 */
class EngineBlock_AttributeAggregator 
{
    const FORMAT_OPENSOCIAL = 'opensocial';
    const FORMAT_SAML       = 'saml';

    protected $_providers = array();

    /**
     * Create a new aggregator for a set of providers
     *
     * @param array $providers
     */
    public function __construct(array $providers)
    {
        $this->_providers = $providers;
    }

    /**
     * Aggregate attributes from all providers and enhance the original attributes with them.
     *
     * @param array  $attributes Existing attributes to enhance
     * @param string $uid        URN for user (example: urn:collab:person:example.edu:john.doe)
     * @param string $format     Format of the attributes to get.
     * @return array             Enhanced attributes
     * @throws EngineBlock_Exception
     */
    public function aggregateFor(array $attributes, $uid, $format = self::FORMAT_SAML)
    {
        /**
         * @var EngineBlock_AttributeProvider_Interface $provider
         */
        foreach ($this->_providers as $provider) {
            $providerAttributes = $provider->getAttributes($uid, $format);

            switch ($provider->getStrategy()) {
                case EngineBlock_AttributeProvider_Interface::STRATEGY_ADD:
                    $attributes = $this->_addAttributes($attributes, $providerAttributes);
                    break;

                case EngineBlock_AttributeProvider_Interface::STRATEGY_MERGE:
                    $attributes = $this->_mergeAttributes($attributes, $providerAttributes);
                    break;

                default:
                    $providerClassName = get_class($provider);
                    throw new EngineBlock_Exception(
                        "We have an attribute provider of type '{$providerClassName}', "
                        . "but no idea how to integrate attribute we get from this provider: " . get_class($provider),
                        EngineBlock_Exception::CODE_CRITICAL
                    );
            }
        }
        return $attributes;
    }

    /**
     * Add new attributes, but don't overwrite existing attributes
     *
     * @param array $ebAttributes       Attributes by EngineBlock (from IdP)
     * @param array $providerAttributes Attributes from provider
     * @return array New attributes to use
     */
    protected function _addAttributes(array $ebAttributes, array $providerAttributes)
    {
        foreach ($providerAttributes as $attribName => $attribValues) {
            if (!isset($ebAttributes[$attribName])) {
                $ebAttributes[$attribName] = $attribValues;
            }
            else {
                // @todo we may need to differentiate between single and multi-valued attributes
                //       for instance the Idp could have provided an e-mail attribute, but
                //       attribute aggregation could add another e-mail.
                //       however we don't want to add a new value for the uid attribute.
                //       So for now, if EB already has an attribute, we ignore attributes from aggregation.
                continue;
            }
        }
        return $ebAttributes;
    }

    /**
     * Merge the attributes, add new ones or overwrite existing ones.
     *
     * @param array $ebAttributes       Attributes by EngineBlock (from IdP)
     * @param array $providerAttributes Attributes from provider
     * @return array New attributes to use
     */
    protected function _mergeAttributes(array $ebAttributes, array $providerAttributes)
    {
        return array_merge_recursive($ebAttributes, $providerAttributes);
    }
}
