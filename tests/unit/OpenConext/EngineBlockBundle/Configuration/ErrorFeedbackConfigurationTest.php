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

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;
use stdClass;

class ErrorFeedbackConfigurationTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function all_links_must_be_an_instance_of_wiki_link()
    {
        $wikiLinks = [
            'no-session-found' => new WikiLink(['a' => 'b'], ['a' => 'https://fallback.uri']),
            'invalid-response' => new WikiLink(['a' => 'b'], ['a' => 'https://fallback.uri']),
            'foo' => new stdClass()
        ];

        $this->expectException(InvalidArgumentException::class);

        new ErrorFeedbackConfiguration($wikiLinks);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function all_links_must_have_a_string_key()
    {
        $wikiLinks = [
            'no-session-found'  => new WikiLink(['a' => 'b'], ['a' => 'https://fallback.uri']),
            1 => new WikiLink(['a' => 'b'], ['a' => 'https://fallback.uri']),
        ];

        $this->expectException(InvalidArgumentException::class);

        new ErrorFeedbackConfiguration($wikiLinks);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function a_link_can_be_queried_for_presence()
    {
        $wikiLinks = [
            'no-session-found' => new WikiLink(['a' => 'b'], ['a' => 'https://fallback.uri']),
            'invalid-response' => new WikiLink(['a' => 'b'], ['a' => 'https://fallback.uri']),
        ];

        $errorFeedbackConfiguration = new ErrorFeedbackConfiguration($wikiLinks);

        $this->assertTrue($errorFeedbackConfiguration->hasWikiLink('no-session-found'));
        $this->assertTrue($errorFeedbackConfiguration->hasWikiLink('invalid-response'));
        $this->assertFalse($errorFeedbackConfiguration->hasWikiLink('not-configured'));
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function a_link_can_be_retrieved()
    {
        $wikiLinks = [
            'no-session-found' => new WikiLink(['a' => 'b'], ['a' => 'https://fallback.uri']),
        ];

        $errorFeedbackConfiguration = new ErrorFeedbackConfiguration($wikiLinks);
        $noSessionFound = $errorFeedbackConfiguration->getWikiLink('no-session-found');
        $this->assertInstanceOf(WikiLink::class, $noSessionFound);
    }
}
