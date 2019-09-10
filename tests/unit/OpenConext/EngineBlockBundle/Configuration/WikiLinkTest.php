<?php

/**
 * Copyright 2014 SURFnet B.V.
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

class WikiLinkTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     * @dataProvider \OpenConext\TestDataProvider::notArray()
     * @param mixed $notArray
     */
    public function wiki_links_is_required_to_be_an_array($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        new WikiLink($notArray, ['a' => 'https://fallback.uri']);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     * @dataProvider \OpenConext\TestDataProvider::notArray()
     * @param mixed $notArray
     */
    public function fallback_must_be_be_an_array_of_non_empty_strings($notArray)
    {
        $this->expectException(InvalidArgumentException::class);

        new WikiLink(['en' => 'https://wiki.co.uk/page1'], $notArray);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function the_provided_links_can_be_retrieved_by_language()
    {
        $link = new WikiLink(
            [
                'en' => 'https://wiki.co.uk/page1',
                'nl' => 'https://wiki.nl/page1',
                'pt' => 'https://wiki.pt/page1',
            ],
            ['en' => 'https://wiki.co.uk/fallback']
        );

        $this->assertEquals('https://wiki.co.uk/page1', $link->getLink('en'));
        $this->assertEquals('https://wiki.nl/page1', $link->getLink('nl'));
        $this->assertEquals('https://wiki.pt/page1', $link->getLink('pt'));
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function when_link_not_found_a_fallback_is_returned()
    {
        $link = new WikiLink(
            [
                'en' => 'https://wiki.co.uk/page1',
            ],
            [
                'en' => 'https://wiki.co.uk/fallback',
                'es' => 'https://wiki.es/fallback'
            ]
        );
        $this->assertEquals('https://wiki.es/fallback', $link->getLink('es'));
    }
}
