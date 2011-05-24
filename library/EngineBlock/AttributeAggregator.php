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
    protected $_providers = array();

    public function __construct(array $providers)
    {
        $this->_providers = $providers;
    }

    public function getAttributes($uid)
    {
        $attributes = array();
        foreach ($this->_providers as $provider) {
            $attributes = array_merge_recursive($attributes, $provider->getAttributes($uid));
        }
        return $attributes;
    }
}
