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
 * Class RequestedAttribute
 * @package OpenConext\EngineBlock\Metadata
 */
class RequestedAttribute
{
    const NAME_FORMAT_URI = 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $nameFormat = self::NAME_FORMAT_URI;

    /**
     * @var null|bool
     */
    public $required = null;

    /**
     * @param $name
     * @param bool $isRequired
     * @param string $nameFormat
     */
    public function __construct($name, $isRequired = false, $nameFormat = self::NAME_FORMAT_URI)
    {
        $this->name = $name;
        $this->nameFormat = $nameFormat;
        $this->required = $isRequired;
    }

    /**
     * A convenience static constructor for the RequestedAttribute.
     * @param array $requestedAttribute
     * @return RequestedAttribute
     */
    public static function fromArray(array $requestedAttribute): RequestedAttribute
    {
        return new self($requestedAttribute["name"], $requestedAttribute["required"], $requestedAttribute["nameFormat"]);
    }
}
