<?php

/**
 * Copyright 2017 SURFnet B.V.
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

use Mockery as m;
use OpenConext\EngineBlockBundle\Pdp\Dto\Request;
use OpenConext\EngineBlockBundle\Pdp\PdpClient;
use PHPUnit_Framework_TestCase as TestCase;

class PdpClientTest extends TestCase
{
    /**
     * @test
     * @group Pdp
     */
    public function a_pdp_client_gives_policy_decisions_based_on_pdp_responses_to_pdp_requests()
    {
        $request = Request::from('subject', 'idp', 'sp', []);
        $denyResponseJson = json_decode(file_get_contents(__DIR__ . '/../fixture/response_deny.json'), true);

        $httpClientMock = m::mock('\OpenConext\EngineBlock\Http\HttpClient');
        $httpClientMock
            ->shouldReceive('post')
            ->once()
            ->with('decide/policy', json_encode($request), m::any())
            ->andReturn($denyResponseJson);

        $pdpClient = new PdpClient($httpClientMock);
        $policyDecision = $pdpClient->giveDecisionBasedOn($request);

        $this->assertFalse($policyDecision->permitsAccess(), 'The Deny response should not permit access, but it does');
        $this->assertFalse(
            $policyDecision->hasStatusMessage(),
            'The Deny response should not have a status message, but it has one'
        );
        $this->assertTrue(
            $policyDecision->hasLocalizedDenyMessage(),
            'The Deny response should have a localized Deny message'
        );
    }
}
