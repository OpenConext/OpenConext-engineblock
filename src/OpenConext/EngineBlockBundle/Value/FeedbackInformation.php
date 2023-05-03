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

namespace OpenConext\EngineBlockBundle\Value;

use OpenConext\EngineBlock\Assert\Assertion;

class FeedbackInformation
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $key
     * @param string $value
     */
    public function __construct($key, $value)
    {
        Assertion::nonEmptyString($key, "The feedbackInfo key can't be empty and must be a string value");
        Assertion::nonEmptyString(
            $value,
            sprintf("The feedbackInfo value for '%s' can't be empty and must be a string value", $key)
        );
        $this->key = $key;
        $this->value = $value;
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get a HTML attribute safe representation of the key
     * @return string
     */
    public function getAttrSafeKey()
    {
        $safeKey = $this->key;
        $safeKey = str_replace(" ", "-", $safeKey);
        $safeKey = strtolower($safeKey);
        return htmlspecialchars($safeKey);
    }

    public function __toString()
    {
        return $this->value;
    }
}
