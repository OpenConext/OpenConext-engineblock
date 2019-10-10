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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use SAML2\AuthnRequest;

class EngineBlock_Test_Saml2_AuthnRequestAnnotationDecoratorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testToString()
    {
        $request = new AuthnRequest();
        $request->setId('TEST123');
        $request->setIssueInstant(0);

        $annotatedRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($request);
        $annotatedRequest->setDebug();

        $this->assertEquals(
            '{"sspMessage":"<?xml version=\"1.0\"?>\n<samlp:AuthnRequest xmlns:samlp=\"urn:oasis:names:tc:SAML:2.0:protocol\" xmlns:saml=\"urn:oasis:names:tc:SAML:2.0:assertion\" ID=\"TEST123\" Version=\"2.0\" IssueInstant=\"1970-01-01T00:00:00Z\"\/>\n","keyId":null,"wasSigned":false,"debug":true,"unsolicited":false,"transparent":false,"deliverByBinding":null}',
            $annotatedRequest->__toString()
        );
    }
}
