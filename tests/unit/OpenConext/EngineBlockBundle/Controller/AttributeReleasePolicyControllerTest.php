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

use EngineBlock_Arp_AttributeReleasePolicyEnforcer;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Service\MetadataService;
use OpenConext\EngineBlockBundle\Controller\Api\AttributeReleasePolicyController;
use OpenConext\EngineBlockBundle\Http\Exception\ApiAccessDeniedHttpException;
use OpenConext\EngineBlockBundle\Http\Exception\BadApiRequestHttpException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AttributeReleasePolicyControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group AttributeReleasePolicy
     * @group AuthorizationChecker
     */
    public function access_is_denied_for_unauthorized_requests()
    {
        $this->expectException(ApiAccessDeniedHttpException::class, 'ROLE_API_USER_PROFILE');

        $metadataService = $this->createDummyMetadataService();
        $arpEnforcer = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();

        $authorizationChecker = $this->mockAuthorizationCheckerDenyingAccessToProfile();

        $arpController = new AttributeReleasePolicyController($authorizationChecker, $metadataService, $arpEnforcer);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);

        $arpController->applyArpAction($request);
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     * @group Json
     */
    public function a_request_that_cannot_be_decoded_as_an_array_is_invalid()
    {
        $this->expectException(BadApiRequestHttpException::class);
        $this->expectExceptionMessage('expected decoded root value to be an array');

        $request = $this->createRequestWithContent('null');

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $this->createDummyMetadataService(),
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $arpController->applyArpAction($request);
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     * @group Json
     */
    public function a_request_that_does_not_contain_the_entity_ids_key_is_invalid()
    {
        $this->expectException(BadApiRequestHttpException::class);
        $this->expectExceptionMessage('key "entityIds" not found');

        $request = $this->createRequestWithContent('{}');

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $this->createDummyMetadataService(),
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $arpController->applyArpAction($request);
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     * @group Json
     */
    public function a_request_that_does_not_have_an_array_of_entity_ids_is_invalid()
    {
        $this->expectException(BadApiRequestHttpException::class);
        $this->expectExceptionMessage('"entityIds" must be a non-empty array');

        $request = $this->createRequestWithContent('{"entityIds": "some-entity-id"}');

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $this->createDummyMetadataService(),
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $arpController->applyArpAction($request);
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     * @group Json
     */
    public function a_request_that_does_not_contain_any_entity_ids_is_invalid()
    {
        $this->expectException(BadApiRequestHttpException::class);
        $this->expectExceptionMessage('"entityIds" must be a non-empty array');

        $request = $this->createRequestWithContent('{"entityIds": []}');

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $this->createDummyMetadataService(),
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $arpController->applyArpAction($request);
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     * @group Json
     */
    public function a_request_that_does_not_contain_the_attributes_key_is_invalid()
    {
        $this->expectException(BadApiRequestHttpException::class);
        $this->expectExceptionMessage('key "attributes" not found');

        $request = $this->createRequestWithContent('{"entityIds": ["some-entity-id"]}');

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $this->createDummyMetadataService(),
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $arpController->applyArpAction($request);
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     * @group Json
     */
    public function a_request_that_does_not_have_a_json_object_of_attributes_is_invalid()
    {
        $this->expectException(BadApiRequestHttpException::class);
        $this->expectExceptionMessage('"attributes" must be a JSON object');

        $request = $this->createRequestWithContent('{"entityIds": ["some-entity-id"], "attributes": "some-attribute"}');

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $this->createDummyMetadataService(),
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $arpController->applyArpAction($request);
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     * @group Json
     */
    public function attributes_should_have_strings_as_keys()
    {
        $this->expectException(BadApiRequestHttpException::class);
        $this->expectExceptionMessage('attributes should have strings as keys and an array of values');

        $request = $this->createRequestWithContent(
            '{"entityIds": ["some-entity-id"], "attributes": ["some-value"]}'
        );

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $this->createDummyMetadataService(),
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $arpController->applyArpAction($request);
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     * @group Json
     */
    public function attributes_should_have_an_array_of_values()
    {
        $this->expectException(BadApiRequestHttpException::class);
        $this->expectExceptionMessage('attributes should have strings as keys and an array of values');

        $request = $this->createRequestWithContent(
            '{"entityIds": ["some-entity-id"], "attributes": {"some-attribute": "some-value"}}'
        );

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $this->createDummyMetadataService(),
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $arpController->applyArpAction($request);
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     */
    public function the_same_attributes_are_returned_as_given_if_no_service_provider_is_found()
    {
        $serializedJsonRequest = json_encode([
            'entityIds'  => [
                'some-entity-id'
            ],
            'attributes' => [
                'some-attribute' => ['a value for this attribute'],
                'another-attribute' => ['another value'],
            ],

        ]);
        $expectedResponse = json_encode([
            'some-entity-id' => [
                'some-attribute' => ['a value for this attribute'],
                'another-attribute' => ['another value'],
            ]
        ]);

        $request = $this->createRequestWithContent($serializedJsonRequest);

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $this->createDummyMetadataService(),
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );

        $response = $arpController->applyArpAction($request);

        $this->assertSame($expectedResponse, $response->getContent());
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     */
    public function the_same_attributes_are_returned_as_given_if_no_arp_is_found()
    {
        $someEntityId    = 'some-entity-id';
        $someServiceProvider = new ServiceProvider($someEntityId);

        $serializedJsonRequest = json_encode([
            'entityIds'  => [
                $someEntityId
            ],
            'attributes' => [
                'some-attribute' => ['a value for this attribute'],
                'another-attribute' => ['another value'],
            ],

        ]);
        $expectedResponse = json_encode([
            $someEntityId => [
                'some-attribute' => ['a value for this attribute'],
                'another-attribute' => ['another value'],
            ]
        ]);

        $request = $this->createRequestWithContent($serializedJsonRequest);

        $metadataRepository = Mockery::mock(MetadataRepositoryInterface::class);
        $metadataRepository->shouldReceive('fetchServiceProviderByEntityId')
            ->with($someEntityId)
            ->andReturn($someServiceProvider);
        $metadataRepository->shouldReceive('fetchServiceProviderArp')
            ->with($someServiceProvider)
            ->andReturn(null);
        $metadataServiceWithServiceProviders = new MetadataService($metadataRepository);

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $metadataServiceWithServiceProviders,
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $response = $arpController->applyArpAction($request);

        $this->assertSame($expectedResponse, $response->getContent());
    }

    /**
     * @test
     * @group AttributeReleasePolicy
     */
    public function only_attributes_specified_in_arp_are_released()
    {
        $someEntityId                 = 'some-entity-id';
        $someServiceProvider          = new ServiceProvider($someEntityId);
        $arpForSomeServiceProvider    = new AttributeReleasePolicy([
            'some-attribute' => ["*"]
        ]);
        $anotherEntityId              = 'another-entity-id';
        $anotherServiceProvider       = new ServiceProvider($anotherEntityId);
        $arpForAnotherServiceProvider = new AttributeReleasePolicy([
            'another-attribute' => ["*"]
        ]);

        $serializedJsonRequest = json_encode([
            'entityIds'  => [
                $someEntityId,
                $anotherEntityId,
            ],
            'attributes' => [
                'some-attribute' => ['a value for this attribute'],
                'another-attribute' => ['another value'],
            ],

        ]);
        $expectedResponse = json_encode([
            $someEntityId => [
                'some-attribute' => ['a value for this attribute'],
            ],
            $anotherEntityId => [
                'another-attribute' => ['another value']
            ]
        ]);

        $request = $this->createRequestWithContent($serializedJsonRequest);

        $metadataRepository = Mockery::mock(MetadataRepositoryInterface::class);
        $metadataRepository->shouldReceive('fetchServiceProviderByEntityId')
            ->with('some-entity-id')
            ->andReturn($someServiceProvider);

        $someServiceProvider->attributeReleasePolicy = $arpForSomeServiceProvider;

        $metadataRepository->shouldReceive('fetchServiceProviderByEntityId')
            ->with($anotherEntityId)
            ->andReturn($anotherServiceProvider);

        $anotherServiceProvider->attributeReleasePolicy = $arpForAnotherServiceProvider;

        $metadataServiceWithServiceProviders = new MetadataService($metadataRepository);

        $arpController = new AttributeReleasePolicyController(
            $this->mockAuthorizationCheckerGrantingAccessToProfile(),
            $metadataServiceWithServiceProviders,
            new EngineBlock_Arp_AttributeReleasePolicyEnforcer
        );
        $response = $arpController->applyArpAction($request);

        $this->assertSame($expectedResponse, $response->getContent());
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    public function mockAuthorizationCheckerGrantingAccessToProfile()
    {
        $authorizationChecker = Mockery::mock(AuthorizationCheckerInterface::class);
        $authorizationChecker->shouldReceive('isGranted')
            ->with('ROLE_API_USER_PROFILE')
            ->andReturn(true);

        return $authorizationChecker;
    }

    /**
     * @return AuthorizationCheckerInterface
     */
    public function mockAuthorizationCheckerDenyingAccessToProfile()
    {
        $authorizationChecker = Mockery::mock(AuthorizationCheckerInterface::class);
        $authorizationChecker->shouldReceive('isGranted')
            ->with('ROLE_API_USER_PROFILE')
            ->andReturn(false);

        return $authorizationChecker;
    }

    /**
     * @param string $content
     * @return Request
     */
    public function createRequestWithContent($content)
    {
        $request = new Request([], [], [], [], [], [], $content);
        $request->setMethod(Request::METHOD_POST);

        return $request;
    }

    /**
     * @return MetadataService
     */
    public function createDummyMetadataService()
    {
        $metadataService = new MetadataService(
            Mockery::mock(MetadataRepositoryInterface::class)->shouldIgnoreMissing()
        );

        return $metadataService;
    }
}
