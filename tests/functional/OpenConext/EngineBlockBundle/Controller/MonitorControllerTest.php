<?php

/**
 * Copyright 2026 SURFnet B.V.
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

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class MonitorControllerTest extends WebTestCase
{
    #[Test]
    #[Group('Monitor')]
    public function internal_info_returns_json()
    {
        $client = self::createClient();
        $client->request('GET', 'https://engine.dev.openconext.local/internal/info');

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    #[Test]
    #[Group('Monitor')]
    public function internal_health_returns_json()
    {
        $client = self::createClient();
        $client->request('GET', 'https://engine.dev.openconext.local/internal/health');

        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame(['status' => 'UP'], $json);
    }
}
