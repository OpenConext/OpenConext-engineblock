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

interface EngineBlock_Attributes_Provider_Interface
{
    const STRATEGY_MERGE = 'merge';
    const STRATEGY_ADD   = 'add';

    const FORMAT_OPENSOCIAL = 'opensocial';
    const FORMAT_SAML       = 'saml';

    /**
     * Retrieve all attributes that the Attributes Provider provides for the
     * given user.
     * @param String $uid The URN of a user, for example
     *                    urn:collab:example.org:niels
     * @param String $format Format of the attributes to get.
     * @return Array An array containing attributes. The keys of the array are
     *               the names of the attributes. Each array element contains
     *               an array with the following elements:
     *               - format: the format of the attribute
     *               - value: the value of the attribute
     *               - source (optional): the URN of the provider of the
     *                 attribute. If source is not present, the current
     *                 AttributesProvider is the source (@see getIdentifier()).
     */
    public function getAttributes($uid, $format = self::FORMAT_SAML);

    public function getStrategy();
}
