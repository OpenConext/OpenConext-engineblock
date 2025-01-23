<?php

/**
 * Copyright 2025 SURFnet B.V.
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
use OpenConext\EngineBlock\Http\Exception\UnreadableResourceException;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\Pdp\PdpClientInterface;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\AuthnRequest;

class EngineBlock_Test_Corto_Filter_Command_EnforcePolicyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testThrowsEngineBlockExceptionIfPolicyCannotBeChecked()
    {
        $this->expectException('EngineBlock_Exception_PdpCheckFailed');
        $this->expectExceptionMessage('Policy Enforcement Point: Could not perform PDP check: Resource could not be read (status code "503")');

        $this->mockPdpClientWithException(new UnreadableResourceException('Resource could not be read (status code "503")'));

        $policy = new EngineBlock_Corto_Filter_Command_EnforcePolicy();

        $request = $this->mockRequest();
        $policy->setRequest($request);

        $repo = Mockery::mock(MetadataRepositoryInterface::class);
        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->expects('getRepository')->andReturn($repo);

        $sp = $this->mockServiceProvider();

        $policy->setServiceProvider($sp);
        $policy->setProxyServer($server);
        $policy->setResponseAttributes([]);

        $policy->setCollabPersonId('foo');

        $idp = Mockery::mock(IdentityProvider::class);
        $idp->entityId = 'bar';
        $policy->setIdentityProvider($idp);

        $policy->execute();
    }

    private function mockServiceProvider(): ServiceProvider
    {
        $sp = Mockery::mock(ServiceProvider::class);
        $sp->entityId = 'bar';
        $sp->shouldReceive('getCoins->isTrustedProxy')->andReturn(false);
        $sp->shouldReceive('getCoins->policyEnforcementDecisionRequired')->andReturn(true);
        return $sp;
    }

    private function mockRequest(): EngineBlock_Saml2_AuthnRequestAnnotationDecorator
    {
        $assertion = new Assertion();
        $request = new AuthnRequest();
        $response = new SAML2\Response();
        $response->setAssertions(array($assertion));
        return new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($request);
    }

    private function mockPdpClientWithException(Throwable $exception): void
    {
        $pdpClient = Mockery::mock(PdpClientInterface::class);
        $pdpClient->expects('requestDecisionFor')->andThrow($exception);

        /** @var EngineBlock_Application_TestDiContainer $container */
        $container = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $container->setPdpClient($pdpClient);
    }

}
