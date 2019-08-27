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

namespace OpenConext\EngineBlock\Metadata;

/**
 * Class Organization
 * @package OpenConext\EngineBlock\Metadata
 */
class Organization
{
    public $name;
    public $displayName;
    public $url;

    /**
     * @param $name
     * @param $displayName
     * @param $url
     */
    public function __construct($name, $displayName, $url)
    {
        $this->displayName = $displayName;
        $this->name = $name;
        $this->url = $url;
    }
}
