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
 * A SAML2 metadata logo.
 * @package OpenConext\EngineBlock\Metadata
 */
class Logo
{
    public $height;
    public $width;
    public $url;

    /**
     * @param string $url
     */
    public function __construct($url, $height = null, $width = null)
    {
        $this->url = $url;
        $this->height = $height;
        $this->width = $width;
    }


    /**
     * A convenience static constructor for the Logo.
     * @param array $logo
     * @return Logo
     */
    public static function fromArray(array $logo): Logo
    {
        return new self($logo["url"], $logo["height"], $logo["width"]);
    }
}
