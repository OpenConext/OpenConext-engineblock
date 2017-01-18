<?php

/**
 * Copyright 2016 SURFnet B.V.
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

use Assert\InvalidArgumentException;
use OpenConext\EngineBlockBundle\Pdp\Dto\Attribute;
use OpenConext\EngineBlockBundle\Pdp\Dto\Request;
use OpenConext\EngineBlockBundle\Pdp\Dto\Request\AccessSubject;
use OpenConext\EngineBlockBundle\Pdp\Dto\Request\Resource;
use OpenConext\Value\Saml\NameIdFormat;
use PHPUnit_Framework_TestCase as TestCase;
use stdClass;

class RequestTest extends TestCase
{
    private $validSubjectId;
    private $validIdpEntityId;
    private $validSpEntityId;
    private $validResponseAttributes;

    public function setUp()
    {
        $this->validSubjectId   = 'subject-id';
        $this->validIdpEntityId = 'https://my-idp.example';
        $this->validSpEntityId  = 'https://my-sp.example';
        $this->validResponseAttributes = [
            ['urn:mace:dir:attribute-def:eduPersonAffiliation' => ['student', 'alumni']]
        ];
    }
    
    /**
     * @test
     * @group Pdp
     *
     * @dataProvider nonStringProvider
     * @param string $invalidSubjectId
     */
    public function a_pdp_requests_subject_id_must_be_a_string($invalidSubjectId)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SubjectId must be a string');

        Request::from(
            $invalidSubjectId,
            $this->validIdpEntityId,
            $this->validSpEntityId,
            $this->validResponseAttributes
        );
    }

    /**
     * @test
     * @group Pdp
     *
     * @dataProvider nonStringProvider
     * @param string $invalidIdpEntityId
     */
    public function a_pdp_requests_idp_entity_id_must_be_a_string($invalidIdpEntityId)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IDPentityID must be a string');

        Request::from(
            $this->validSubjectId,
            $invalidIdpEntityId,
            $this->validSpEntityId,
            $this->validResponseAttributes
        );
    }

    /**
     * @test
     * @group Pdp
     *
     * @dataProvider nonStringProvider
     * @param $invalidSpEntityId
     */
    public function a_pdp_requests_sp_entity_id_must_be_a_string($invalidSpEntityId)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SPentityID must be a string');

        Request::from($this->validSubjectId, $this->validIdpEntityId, $invalidSpEntityId, $this->validResponseAttributes);
    }

    /**
     * @test
     * @group Pdp
     */
    public function a_pdp_requests_response_attribute_keys_must_be_strings()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The keys of the Response attributes must be strings');

        $responseAttributesWithNonStringKeys = [
            1 => ['some-attribute', 'another-attribute'],
            2 => ['an-unrelated-attribute']
        ];

        Request::from(
            $this->validSubjectId,
            $this->validIdpEntityId,
            $this->validSpEntityId,
            $responseAttributesWithNonStringKeys
        );
    }

    /**
     * @test
     * @group Pdp
     *
     * @dataProvider nonArrayProvider
     */
    public function a_pdp_requests_response_attribute_values_must_be_arrays($nonArray)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The values of the Response attributes must be arrays');

        $responseAttributesWithNonArrayValues = [
            'urn:test:some-attribute' => $nonArray,
        ];

        Request::from(
            $this->validSubjectId,
            $this->validIdpEntityId,
            $this->validSpEntityId,
            $responseAttributesWithNonArrayValues
        );
    }

    /**
     * @test
     * @group Pdp
     */
    public function a_pdp_request_is_built_correctly()
    {
        $resourceAttributeValues = [
            'SPentityID' => 'avans_sp',
            'IDPentityID' => 'avans_idp',
        ];
        $accessSubjectAttributeValues = [
            NameIdFormat::UNSPECIFIED => 'an-unspecified-name-id',
            'urn:mace:dir:attribute-def:eduPersonAffiliation' => 'student',
        ];

        $expectedRequest = $this->buildPdpRequest($resourceAttributeValues, $accessSubjectAttributeValues);

        $actualRequest = Request::from(
            $accessSubjectAttributeValues[NameIdFormat::UNSPECIFIED],
            $resourceAttributeValues['IDPentityID'],
            $resourceAttributeValues['SPentityID'],
            ['urn:mace:dir:attribute-def:eduPersonAffiliation' => ['student']]
        );

        $this->assertEquals($expectedRequest, $actualRequest);
    }

    /**
     * @test
     * @group Pdp
     */
    public function a_pdp_request_is_serialized_correctly()
    {
        $fixturePath = __DIR__.'/../fixture/request.json';

        $expectedJson = json_encode(
            json_decode(
                file_get_contents($fixturePath)
            ), JSON_PRETTY_PRINT
        );

        $resourceAttributeValues = [
            'SPentityID' => 'avans_sp',
            'IDPentityID' => 'avans_idp',
        ];
        $accessSubjectAttributeValues = [
            NameIdFormat::UNSPECIFIED => 'an-unspecified-name-id',
            'urn:mace:dir:attribute-def:eduPersonAffiliation' => 'student',
        ];

        $request = $this->buildPdpRequest($resourceAttributeValues, $accessSubjectAttributeValues);

        $actualJson = json_encode($request, JSON_PRETTY_PRINT);

        $this->assertSame(
            $expectedJson,
            $actualJson,
            'The serialized PDP request does not match the expected json PDP request'
        );
    }

    /**
     * @param $resourceAttributeValues
     * @param $accessSubjectAttributeValues
     * @return Request
     */
    private function buildPdpRequest($resourceAttributeValues, $accessSubjectAttributeValues)
    {
        $expectedRequest                = new Request;
        $expectedRequest->resource      = new Resource;
        $expectedRequest->accessSubject = new AccessSubject;

        foreach ($resourceAttributeValues as $id => $value) {
            $resourceAttribute                       = new Attribute;
            $resourceAttribute->attributeId          = $id;
            $resourceAttribute->value                = $value;
            $expectedRequest->resource->attributes[] = $resourceAttribute;
        }

        foreach ($accessSubjectAttributeValues as $id => $value) {
            $accessSubjectAttribute                       = new Attribute;
            $accessSubjectAttribute->attributeId          = $id;
            $accessSubjectAttribute->value                = $value;
            $expectedRequest->accessSubject->attributes[] = $accessSubjectAttribute;
        }

        return $expectedRequest;
    }

    public function nonStringProvider()
    {
        return [
            'null' => [null],
            'boolean' => [true],
            'array' => [[]],
            'object' => [new stdClass()],
            'integer' => [123],
            'float' => [1.23],
        ];
    }

    public function nonArrayProvider()
    {
        return [
            'null' => [null],
            'boolean' => [true],
            'string' => ['my-string'],
            'object' => [new stdClass()],
            'integer' => [123],
            'float' => [1.23],
        ];
    }
}
