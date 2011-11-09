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
 * The Surfnet_Identity class is responsible for storing
 * the metadata of a user.
 *
 * Usually the metadata is provided by an external source
 * like an Identity Provider.
 *
 * @author marc
 */
class SurfConext_Identity
{
    /**
     * Unique identifier for this identity
     *
     * @var string
     */
    public $id;

    /**
     * Display name to use in the interface for this user.
     * 
     * @var string
     */
    public $displayName;

    /**
     * @param string $id Unique Identifier
     */
    public function __construct($id)
    {
        $this->id = $id;
    }
}
