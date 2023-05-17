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

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class AttributeReleasePolicyControllerApiTest extends WebTestCase
{
    public function setUp(): void
    {
        $this->clearMetadataFixtures();
    }

    public function tearDown(): void
    {
        $this->clearMetadataFixtures();
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     */
    public function authentication_is_required_for_applying_arps()
    {
        $unauthenticatedClient = static::createClient();
        $unauthenticatedClient->request('POST', 'https://engine-api.vm.openconext.org/arp');
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED,  $unauthenticatedClient);
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     *
     * @dataProvider invalidHttpMethodProvider
     * @param string $invalidHttpMethod
     */
    public function only_post_requests_are_allowed_when_applying_arp($invalidHttpMethod)
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request($invalidHttpMethod, 'https://engine-api.vm.openconext.org/arp');
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     */
    public function cannot_apply_arp_if_user_does_not_have_profile_role()
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'no_roles',
            'PHP_AUTH_PW' => 'no_roles',
        ]);

        $client->request('POST', 'https://engine-api.vm.openconext.org/arp');
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     *
     * @dataProvider invalidJsonPayloadProvider
     * @param string $invalidJsonPayload
     */
    public function cannot_push_invalid_content_to_the_arp_api($invalidJsonPayload)
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $client->request(
            'POST',
            'https://engine-api.vm.openconext.org/arp',
            [],
            [],
            [],
            $invalidJsonPayload
        );
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $client);

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     */
    public function all_attributes_are_released_through_the_arp_api_if_no_arp_is_found_for_a_service_provider()
    {
        $spEntityId = 'https://my-test-sp.test';
        $attributes = [
            'attribute-key' => ['attribute-value']
        ];

        $serviceProvider = new ServiceProvider($spEntityId);
        $this->addServiceProviderFixture($serviceProvider);

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $arpRequestData = [
            'entityIds'  => [
                $spEntityId
            ],
            'attributes' => $attributes,
        ];

        $client->request(
            'POST',
            'https://engine-api.vm.openconext.org/arp',
            [],
            [],
            [],
            json_encode($arpRequestData)
        );

        $this->assertStatusCode(Response::HTTP_OK, $client);

        $expectedResponseData = [
            $spEntityId => $attributes
        ];
        $responseContent = $client->getResponse()->getContent();
        $this->assertSame($expectedResponseData, json_decode($responseContent, true));

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     */
    public function arps_are_applied_to_sps_and_attributes_by_the_arp_api()
    {
        $spNotReceivingSpecialAttributeEntityId = 'https://sp-that-does-not-receive-special-attribute.test';
        $spReceivingSpecialAttributeEntityId = 'https://sp-that-receives-special-attribute.test';
        $attributes = [
            'attribute-key' => ['attribute-value'],
            'special-attribute' => ['secret-value'],
        ];
        $arpWithoutSpecialAttribute = new AttributeReleasePolicy([
            'attribute-key' => ['*'],
        ]);
        $arpWithSpecialAttribute = new AttributeReleasePolicy([
            'attribute-key' => ['*'],
            'special-attribute' => ['*'],
        ]);

        $spNotReceivingSpecialAttribute = $this->createServiceProviderWithArp(
            $spNotReceivingSpecialAttributeEntityId,
            $arpWithoutSpecialAttribute
        );
        $spReceivingSpecialAttribute = $this->createServiceProviderWithArp(
            $spReceivingSpecialAttributeEntityId,
            $arpWithSpecialAttribute
        );
        $this->addServiceProviderFixture($spNotReceivingSpecialAttribute);
        $this->addServiceProviderFixture($spReceivingSpecialAttribute);

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $arpRequestData = [
            'entityIds'  => [
                $spReceivingSpecialAttributeEntityId,
                $spNotReceivingSpecialAttributeEntityId,
            ],
            'attributes' => $attributes,
        ];

        $client->request(
            'POST',
            'https://engine-api.vm.openconext.org/arp',
            [],
            [],
            [],
            json_encode($arpRequestData)
        );

        $this->assertStatusCode(Response::HTTP_OK, $client);

        $expectedResponseData = [
            'https://sp-that-receives-special-attribute.test' => [
                'attribute-key' => ['attribute-value'],
                'special-attribute' => ['secret-value'],
            ],
            'https://sp-that-does-not-receive-special-attribute.test' => [
                'attribute-key' => ['attribute-value'],
            ]
        ];
        $responseContent = $client->getResponse()->getContent();
        $this->assertSame($expectedResponseData, json_decode($responseContent, true));

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     */
    public function arps_matching_on_exact_keys_are_applied_to_sps_and_attributes_by_the_arp_api()
    {
        $spNotReceivingSpecialAttributeEntityId = 'https://sp-that-does-not-receive-special-attribute.test';
        $spReceivingSpecialAttributeEntityId = 'https://sp-that-receives-special-attribute.test';
        $attributes = [
            'attribute-key' => ['attribute-value'],
            'special-attribute' => ['secret-value'],
        ];
        $arpWithoutMatchingSpecialAttribute = new AttributeReleasePolicy([
            'attribute-key' => ['*'],
            'special-attribute' => ['normal-value'],
        ]);
        $arpWithMatchinSpecialAttribute = new AttributeReleasePolicy([
            'attribute-key' => ['*'],
            'special-attribute' => ['secret-value'],
        ]);

        $spNotReceivingSpecialAttribute = $this->createServiceProviderWithArp(
            $spNotReceivingSpecialAttributeEntityId,
            $arpWithoutMatchingSpecialAttribute
        );
        $spReceivingSpecialAttribute = $this->createServiceProviderWithArp(
            $spReceivingSpecialAttributeEntityId,
            $arpWithMatchinSpecialAttribute
        );
        $this->addServiceProviderFixture($spNotReceivingSpecialAttribute);
        $this->addServiceProviderFixture($spReceivingSpecialAttribute);

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $arpRequestData = [
            'entityIds'  => [
                $spReceivingSpecialAttributeEntityId,
                $spNotReceivingSpecialAttributeEntityId,
            ],
            'attributes' => $attributes,
        ];

        $client->request(
            'POST',
            'https://engine-api.vm.openconext.org/arp',
            [],
            [],
            [],
            json_encode($arpRequestData)
        );

        $this->assertStatusCode(Response::HTTP_OK, $client);

        $expectedResponseData = [
            'https://sp-that-receives-special-attribute.test' => [
                'attribute-key' => ['attribute-value'],
                'special-attribute' => ['secret-value'],
            ],
            'https://sp-that-does-not-receive-special-attribute.test' => [
                'attribute-key' => ['attribute-value'],
            ]
        ];
        $responseContent = $client->getResponse()->getContent();
        $this->assertSame($expectedResponseData, json_decode($responseContent, true));

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    /**
     * @test
     * @group Api
     * @group Arp
     * @group Profile
     */
    public function arps_matching_on_partial_keys_are_applied_to_sps_and_attributes_by_the_arp_api()
    {
        $spNotReceivingSpecialAttributeEntityId = 'https://sp-that-does-not-receive-special-attribute.test';
        $spReceivingSpecialAttributeEntityId = 'https://sp-that-receives-special-attribute.test';
        $attributes = [
            'attribute-key' => ['attribute-value'],
            'special-attribute' => ['secret-value'],
        ];
        $arpWithoutMatchingSpecialAttribute = new AttributeReleasePolicy([
            'attribute-key' => ['*'],
            'special-attribute' => ['normal-*'],
        ]);
        $arpWithMatchinSpecialAttribute = new AttributeReleasePolicy([
            'attribute-key' => ['*'],
            'special-attribute' => ['secret-*'],
        ]);

        $spNotReceivingSpecialAttribute = $this->createServiceProviderWithArp(
            $spNotReceivingSpecialAttributeEntityId,
            $arpWithoutMatchingSpecialAttribute
        );
        $spReceivingSpecialAttribute = $this->createServiceProviderWithArp(
            $spReceivingSpecialAttributeEntityId,
            $arpWithMatchinSpecialAttribute
        );
        $this->addServiceProviderFixture($spNotReceivingSpecialAttribute);
        $this->addServiceProviderFixture($spReceivingSpecialAttribute);

        $client = static::createClient([], [
            'PHP_AUTH_USER' => $this->getContainer()->getParameter('api.users.profile.username'),
            'PHP_AUTH_PW' => $this->getContainer()->getParameter('api.users.profile.password'),
        ]);

        $arpRequestData = [
            'entityIds'  => [
                $spReceivingSpecialAttributeEntityId,
                $spNotReceivingSpecialAttributeEntityId,
            ],
            'attributes' => $attributes,
        ];

        $client->request(
            'POST',
            'https://engine-api.vm.openconext.org/arp',
            [],
            [],
            [],
            json_encode($arpRequestData)
        );

        $this->assertStatusCode(Response::HTTP_OK, $client);

        $expectedResponseData = [
            'https://sp-that-receives-special-attribute.test' => [
                'attribute-key' => ['attribute-value'],
                'special-attribute' => ['secret-value'],
            ],
            'https://sp-that-does-not-receive-special-attribute.test' => [
                'attribute-key' => ['attribute-value'],
            ]
        ];
        $responseContent = $client->getResponse()->getContent();
        $this->assertSame($expectedResponseData, json_decode($responseContent, true));

        $isContentTypeJson =  $client->getResponse()->headers->contains('Content-Type', 'application/json');
        $this->assertTrue($isContentTypeJson, 'Response should have Content-Type: application/json header');
    }

    public function invalidHttpMethodProvider()
    {
        return [
            'GET' => ['GET'],
            'DELETE' => ['DELETE'],
            'HEAD' => ['HEAD'],
            'PUT' => ['PUT'],
            'OPTIONS' => ['OPTIONS']
        ];
    }

    public function invalidJsonPayloadProvider()
    {
        return [
            'string body' => ['"not-an-object"'],
            'integer body' => ['123'],
            'empty object body' => ['{}'],
            'entityIds: string' => [json_encode([
                'entityIds' => 'not-an-object',
                'attributes'  => [],
            ])],
            'entityIds: integer' => [json_encode([
                'entityIds' => 123,
                'attributes'  => [],
            ])],
            'entityIds: null' => [json_encode([
                'entityIds' => null,
                'attributes'  => [],
            ])],
            'entityIds: empty array' => [json_encode([
                'entityIds' => [],
                'attributes'  => [],
            ])],
            'attributes: string' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => 'not-an-object',
            ])],
            'attributes: integer' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => 123,
            ])],
            'attributes: null' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => null,
            ])],
            'attributes: non-string key' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => [1 => ['attribute-value']],
            ])],
            'attributes: string value (non-array)' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => ['attribute-key' => 'attribute-value'],
            ])],
            'attributes: integer value (non-array)' => [json_encode([
                'entityIds'  => ['https://test.idp.test'],
                'attributes' => ['attribute-key' => 1],
            ])],
        ];
    }

    private function assertStatusCode($expectedStatusCode, Client $client)
    {
        $this->assertEquals($expectedStatusCode, $client->getResponse()->getStatusCode());
    }

    private function getContainer() : ContainerInterface
    {
        self::bootKernel();
        return self::$kernel->getContainer();
    }

    private function createServiceProviderWithArp($entityId, AttributeReleasePolicy $attributeReleasePolicy)
    {
        $sp = new ServiceProvider($entityId);
        $sp->attributeReleasePolicy = $attributeReleasePolicy;
        return $sp;
    }

    private function addServiceProviderFixture(ServiceProvider $serviceProvider)
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $em->persist($serviceProvider);
        $em->flush();
    }

    private function clearMetadataFixtures()
    {
        $queryBuilder = $this->getContainer()->get('doctrine')->getConnection()->createQueryBuilder();
        $queryBuilder
            ->delete('sso_provider_roles_eb5')
            ->execute();
    }
}
