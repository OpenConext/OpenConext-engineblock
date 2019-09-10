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

namespace OpenConext\EngineBlockBundle\Configuration;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as UnitTest;

class IdPContactPageTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     * @dataProvider \OpenConext\TestDataProvider::notString()
     * @param mixed $notString
     */
    public function idp_contact_page_is_required_to_be_a_string($notString)
    {
        $this->expectException(InvalidArgumentException::class);

        new IdPContactPage($notString);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function the_provided_links_can_be_retrieved_by_language()
    {
        $contactPage = new IdPContactPage('clock_issue');
        $this->assertEquals('clock_issue', $contactPage->getPageIdentifier());
    }
}
