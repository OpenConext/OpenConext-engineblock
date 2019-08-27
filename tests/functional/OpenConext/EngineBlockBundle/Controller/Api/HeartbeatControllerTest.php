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

namespace OpenConext\EngineBlockBundle\Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class HeartbeatControllerTest extends WebTestCase
{
    /**
     * @test
     * @group Api
     */
    public function engineblock_has_a_heartbeat()
    {
        $client = $this->createClient();
        $client->request('GET', 'https://engine-api.vm.openconext.org/');
        $this->assertStatusCode(Response::HTTP_OK, $client);
    }
}
