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

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class MetadataControllerTest extends WebTestCase
{
    public function tearDown(): void
    {
        $this->clearMetadataFixtures();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function authentication_is_required_for_accessing_the_metadata_api()
    {
        $unauthenticatedClient = static::createClient();
        $unauthenticatedClient->request('GET', 'https://engine-api.dev.openconext.local/metadata/idp?entity-id=urn:test');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, $unauthenticatedClient);
    }

    /**
     * @test
     */
    public function cannot_access_if_user_does_not_have_profile_role()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'no_roles',
            'PHP_AUTH_PW' => 'no_roles',
        ]);

        $client->request('GET', 'https://engine-api.dev.openconext.local/metadata/idp?entity-id=urn:test');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);
        $isContentTypeJson = $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     */
    public function cannot_access_if_feature_disabled()
    {
        $client = $this->createAuthorizedProfileClient();

        $mock = new FeatureConfiguration([
            'api.metadata_api' => false,
        ]);
        $client->getContainer()->set('OpenConext\\EngineBlockBundle\\Configuration\\FeatureConfiguration', $mock);

        $client->request('GET', 'https://engine-api.dev.openconext.local/metadata/idp?entity-id=urn:test');
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);
    }

    /**
     * @test
     */
    public function returns_not_found_when_idp_missing()
    {
        $client = $this->createAuthorizedProfileClient();

        $client->request('GET', 'https://engine-api.dev.openconext.local/metadata/idp?entity-id=https://does-not-exist.example');
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $client);
    }

    /**
     * @test
     */
    public function only_get_requests_are_allowed()
    {
        $client = $this->createAuthorizedProfileClient();
        $client->request('POST', 'https://engine-api.dev.openconext.local/metadata/idp?entity-id=urn:test');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson = $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    private function assertStatusCode($expectedStatusCode, KernelBrowser $client)
    {
        $this->assertEquals($expectedStatusCode, $client->getResponse()->getStatusCode());
    }

    private function createAuthorizedProfileClient(): KernelBrowser
    {
        $client = static::createClient();
        $client->setServerParameters([
            'PHP_AUTH_USER' => self::getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => self::getContainer()->getParameter('api.users.profile.password'),
        ]);
        return $client;
    }

    private function addServiceProviderFixture(ServiceProvider $serviceProvider)
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($serviceProvider);
        $em->flush();
    }

    private function clearMetadataFixtures()
    {
        $queryBuilder = self::getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('sso_provider_roles_eb5')
            ->execute();
    }
}
