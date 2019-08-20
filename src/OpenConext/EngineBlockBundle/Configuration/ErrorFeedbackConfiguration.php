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

class ErrorFeedbackConfiguration implements ErrorFeedbackConfigurationInterface
{
    /**
     * @var Feature[]
     */
    private $wikiLinks;

    /**
     * @param Feature[] $wikiLinks indexed by feature key
     */
    public function __construct(array $wikiLinks)
    {
        Assertion::allIsInstanceOf($wikiLinks, WikiLink::class);
        Assertion::allString(array_keys($wikiLinks), 'All keys for wikiLinks must be a string (the page identifier the wiki link is intended for).');

        $this->wikiLinks = $wikiLinks;
    }

    /**
     * @param string $page
     * @return bool
     */
    public function hasWikiLink($page)
    {
        Assertion::nonEmptyString($page, 'page');

        return array_key_exists($page, $this->wikiLinks);
    }

    /**
     * @param string $page
     * @return WikiLink
     */
    public function getWikiLink($page)
    {
        Assertion::nonEmptyString($page, 'page');
        return $this->wikiLinks[$page];
    }
}
