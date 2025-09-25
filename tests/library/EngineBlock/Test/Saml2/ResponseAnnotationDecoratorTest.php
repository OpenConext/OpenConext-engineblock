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

use SAML2\Response;
use SAML2\Utils;

class EngineBlock_Test_Saml2_ResponseAnnotationDecoratorTest extends \PHPUnit\Framework\TestCase
{
    public function test_relaystate_persists_through_serialization()
    {
        $response = new Response();
        $response->setId('RS1');
        $response->setIssueInstant(Utils::xsDateTimeToTimestamp('2023-01-01T00:00:00Z'));
        $response->setRelayState('ss:mem:response-relay-456');

        $decorator = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);

        $serialized = serialize($decorator);
        $restored = unserialize($serialized);

        $this->assertEquals('ss:mem:response-relay-456', $restored->getRelayState(), 'RelayState should survive serialization in Response');
    }

}
