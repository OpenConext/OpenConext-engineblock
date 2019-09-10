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

use Assert\Assertion;

class MultilingualValue
{
    private $language;

    private $value;

    /**
     * MultilingualValue constructor.
     * @param string $value
     * @param string $language
     * @throws \Assert\AssertionFailedException
     */
    public function __construct($value, $language)
    {
        Assertion::string(
            $value,
            'The \'value\' of a MultilingualValue should be a string'
        );
        Assertion::string(
            $language,
            'The \'language\' of a MultilingualValue should be a string'
        );

        $this->value = $value;
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
