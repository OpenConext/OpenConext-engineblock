<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Configuration;

use OpenConext\EngineBlock\Assert\Assertion;

final class WikiLink
{
    /**
     * @var string
     */
    private $fallback;

    /**
     * The Wiki links keyed on language
     * @var string[]
     */
    private $links;

    /**
     * WikiLink constructor.
     * @param string[] $links
     * @param $fallback
     * @throws \Assert\AssertionFailedException
     */
    public function __construct($links, $fallback)
    {
        Assertion::allNonEmptyString($links, 'links');
        Assertion::nonEmptyString($fallback, 'fallback');

        $this->links  = $links;
        $this->fallback = $fallback;
    }

    /**
     * Load the wiki link for a given languae, falls back on the fallback wiki link if the language can not be found
     * @return string
     */
    public function getLink($language)
    {
        if (isset($this->links[$language])) {
            return $this->links[$language];
        }

        return $this->fallback;
    }
}
