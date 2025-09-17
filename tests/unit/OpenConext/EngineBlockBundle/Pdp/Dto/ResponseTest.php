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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\Pdp\Dto\Attribute;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\AssociatedAdvice;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\AttributeAssignment;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\Category;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\PolicyIdentifier;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\PolicyIdReference;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\PolicySetIdReference;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\Status;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\StatusCode;
use OpenConext\EngineBlockBundle\Pdp\PolicyDecision;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[\PHPUnit\Framework\Attributes\Group('Pdp')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_pdp_response_without_a_response_key_is_invalid()
    {
        $this->expectException(\OpenConext\EngineBlockBundle\Exception\InvalidPdpResponseException::class);
        $this->expectExceptionMessage('Key: Response was not found in the PDP response');

        $responseJson = file_get_contents(__DIR__ . '/../fixture/invalid/response_without_response_key.json');

        Response::fromData(json_decode($responseJson, true));
    }

    #[\PHPUnit\Framework\Attributes\Group('Pdp')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_pdp_response_without_a_response_key_as_an_array_is_invalid()
    {
        $this->expectException(\OpenConext\EngineBlockBundle\Exception\InvalidPdpResponseException::class);
        $this->expectExceptionMessage('Response is not an array');

        $responseJson = file_get_contents(__DIR__ . '/../fixture/invalid/response_without_response_array.json');

        Response::fromData(json_decode($responseJson, true));
    }

    #[\PHPUnit\Framework\Attributes\Group('Pdp')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_pdp_response_with_an_empty_response_is_invalid()
    {
        $this->expectException(\OpenConext\EngineBlockBundle\Exception\InvalidPdpResponseException::class);
        $this->expectExceptionMessage('No response data found');

        $responseJson = file_get_contents(__DIR__ . '/../fixture/invalid/response_with_empty_response.json');

        Response::fromData(json_decode($responseJson, true));
    }

    #[\PHPUnit\Framework\Attributes\Group('Pdp')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_pdp_response_without_a_status_is_invalid()
    {
        $this->expectException(\OpenConext\EngineBlockBundle\Exception\InvalidPdpResponseException::class);
        $this->expectExceptionMessage('Key: Status was not found in the PDP response');

        $responseJson = file_get_contents(__DIR__ . '/../fixture/invalid/response_without_status_key.json');

        Response::fromData(json_decode($responseJson, true));
    }

    #[\PHPUnit\Framework\Attributes\Group('Pdp')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_pdp_response_without_a_policy_identifier_is_invalid()
    {
        $this->expectException(\OpenConext\EngineBlockBundle\Exception\InvalidPdpResponseException::class);
        $this->expectExceptionMessage('Key: PolicyIdentifier was not found in the PDP response');

        $responseJson = file_get_contents(__DIR__ . '/../fixture/invalid/response_without_policy_identifier_key.json');

        Response::fromData(json_decode($responseJson, true));
    }

    #[\PHPUnit\Framework\Attributes\Group('Pdp')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_pdp_response_without_a_decision_is_invalid()
    {
        $this->expectException(\OpenConext\EngineBlockBundle\Exception\InvalidPdpResponseException::class);
        $this->expectExceptionMessage('Key: Decision was not found in the PDP response');

        $responseJson = file_get_contents(__DIR__ . '/../fixture/invalid/response_without_decision_key.json');

        Response::fromData(json_decode($responseJson, true));
    }

    /**
     * @param string $fixtureName
     * @param Response $expectedResponse
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('pdpResponseProvider')]
    #[\PHPUnit\Framework\Attributes\Group('Pdp')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function pdp_responses_are_deserialized_correctly($fixtureName, $expectedResponse)
    {
        $responseString = file_get_contents(__DIR__.'/../fixture/response_'. $fixtureName . '.json');

        $actualResponse = Response::fromData(json_decode($responseString, true));

        $this->assertEquals(
            $expectedResponse,
            $actualResponse,
            'The contents of the actual deserialized PDP response do not match the contents of the expected PDP response'
        );
    }

    public static function pdpResponseProvider()
    {
        return [
            'Decision: Deny'          => ['deny', self::buildDenyResponse()],
            'Decision: Permit'        => ['permit', self::buildPermitResponse()],
            'Decision: NotApplicable' => ['not_applicable', self::buildNotApplicableResponse()],
            'Decision: Indeterminate' => ['indeterminate', self::buildIndeterminateResponse()],
        ];
    }

    private static function buildDenyResponse()
    {
        $response = new Response;

        $response->status                    = new Status;
        $response->status->statusCode        = new StatusCode;
        $response->status->statusCode->value = 'urn:oasis:names:tc:xacml:1.0:status:ok';

        $category                       = new Category;
        $category->categoryId           = 'urn:mace:dir:attribute-def:eduPersonAffiliation';
        $categoryAttribute              = new Attribute;
        $categoryAttribute->attributeId = 'urn:mace:dir:attribute-def:eduPersonAffiliation';
        $categoryAttribute->value       = 'student';
        $categoryAttribute->dataType    = 'http://www.w3.org/2001/XMLSchema#string';
        $category->attributes           = [$categoryAttribute];
        $response->categories             = [$category];

        $associatedAdvice                   = new AssociatedAdvice;
        $attributeAssignmentEn              = new AttributeAssignment();
        $attributeAssignmentEn->category    = 'urn:oasis:names:tc:xacml:3.0:attribute-category:resource';
        $attributeAssignmentEn->attributeId = 'DenyMessage:en';
        $attributeAssignmentEn->value       = 'Students do not have access to this resource';
        $attributeAssignmentEn->dataType    = 'http://www.w3.org/2001/XMLSchema#string';
        $attributeAssignmentNl              = new AttributeAssignment();
        $attributeAssignmentNl->category    = 'urn:oasis:names:tc:xacml:3.0:attribute-category:resource';
        $attributeAssignmentNl->attributeId = 'DenyMessage:nl';
        $attributeAssignmentNl->value       = 'Studenten hebben geen toegang tot deze dienst';
        $attributeAssignmentNl->dataType    = 'http://www.w3.org/2001/XMLSchema#string';
        $associatedAdvice->attributeAssignments = [$attributeAssignmentEn, $attributeAssignmentNl];
        $associatedAdvice->id = 'urn:surfconext:xacml:policy:id:openconext_pdp_test_deny_policy_xml';
        $response->associatedAdvices = [$associatedAdvice];

        $response->policyIdentifier = new PolicyIdentifier();
        $policySetIdReference = new PolicySetIdReference();
        $policySetIdReference->version = '1.0';
        $policySetIdReference->id = 'urn:openconext:pdp:root:policyset';
        $response->policyIdentifier->policySetIdReference = [$policySetIdReference];
        $policyIdReference = new PolicyIdReference();
        $policyIdReference->version = '1';
        $policyIdReference->id = 'urn:surfconext:xacml:policy:id:openconext_pdp_test_deny_policy_xml';
        $response->policyIdentifier->policyIdReference = [$policyIdReference];

        $response->decision = PolicyDecision::DECISION_DENY;

        return $response;
    }

    private static function buildPermitResponse()
    {
        $response = new Response;

        $response->status                    = new Status;
        $response->status->statusCode        = new StatusCode;
        $response->status->statusCode->value = 'urn:oasis:names:tc:xacml:1.0:status:ok';

        $category = new Category();
        $category->categoryId = 'urn:mace:terena.org:attribute-def:edu';
        $categoryAttribute = new Attribute;
        $categoryAttribute->attributeId = 'urn:mace:terena.org:attribute-def:edu';
        $categoryAttribute->value = 'what';
        $categoryAttribute->dataType = 'http://www.w3.org/2001/XMLSchema#string';
        $category->attributes = [$categoryAttribute];
        $response->categories = [$category];

        $response->policyIdentifier = new PolicyIdentifier();
        $policySetIdReference = new PolicySetIdReference();
        $policySetIdReference->version = '1.0';
        $policySetIdReference->id = 'urn:openconext:pdp:root:policyset';
        $response->policyIdentifier->policySetIdReference = [$policySetIdReference];
        $policyIdReference = new PolicyIdReference();
        $policyIdReference->version = '1';
        $policyIdReference->id = 'urn:surfconext:xacml:policy:id:openconext_pdp_test_multiple_or_policy_xml';
        $response->policyIdentifier->policyIdReference = [$policyIdReference];

        $response->decision = PolicyDecision::DECISION_PERMIT;

        return $response;
    }

    private static function buildNotApplicableResponse()
    {
        $response = new Response;

        $response->status                    = new Status;
        $response->status->statusCode        = new StatusCode;
        $response->status->statusCode->value = 'urn:oasis:names:tc:xacml:1.0:status:ok';

        $response->policyIdentifier = new PolicyIdentifier();
        $policySetIdReference = new PolicySetIdReference();
        $policySetIdReference->version = '1.0';
        $policySetIdReference->id = '5554cfff-2aa9-4bf0-a9dd-507239939d05';
        $response->policyIdentifier->policySetIdReference = [$policySetIdReference];

        $response->decision = PolicyDecision::DECISION_NOT_APPLICABLE;

        return $response;
    }

    private static function buildIndeterminateResponse()
    {
        $response = new Response;

        $response->status                    = new Status;
        $response->status->statusDetail      = '<MissingAttributeDetail Category=\"urn:oasis:names:tc:xacml:1.0:subject-category:access-subject\" AttributeId=\"urn:mace:dir:attribute-def:eduPersonAffiliation\" DataType=\"http://www.w3.org/2001/XMLSchema#string\"></MissingAttributeDetail>';
        $response->status->statusCode        = new StatusCode;
        $response->status->statusCode->value = 'urn:oasis:names:tc:xacml:1.0:status:missing-attribute';
        $response->status->statusMessage     = 'Missing required attribute';

        $response->policyIdentifier = new PolicyIdentifier();
        $policySetIdReference = new PolicySetIdReference();
        $policySetIdReference->version = '1.0';
        $policySetIdReference->id = '5ea058ea-002c-4d52-a93c-4008df7d84b8';
        $response->policyIdentifier->policySetIdReference = [$policySetIdReference];
        $policyIdReference = new PolicyIdReference();
        $policyIdReference->version = '1';
        $policyIdReference->id = 'urn:surfconext:xacml:policy:id:openconext.pdp.test.deny.policy.xml';
        $response->policyIdentifier->policyIdReference = [$policyIdReference];

        $response->decision = PolicyDecision::DECISION_INDETERMINATE;

        return $response;
    }
}
