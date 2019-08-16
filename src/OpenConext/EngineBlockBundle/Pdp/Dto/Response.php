<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Pdp\Dto;

use OpenConext\EngineBlockBundle\Exception\InvalidPdpResponseException;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\AssociatedAdvice;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\AttributeAssignment;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\Category;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\PolicyIdentifier;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\PolicyIdReference;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\PolicySetIdReference;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\Status;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response\StatusCode;

final class Response
{
    /**
     * @var Status
     */
    public $status;

    /**
     * @var Category[]
     */
    public $categories;

    /**
     * @var AssociatedAdvice[]
     */
    public $associatedAdvices;

    /**
     * @var PolicyIdentifier
     */
    public $policyIdentifier;

    /**
     * @var string
     */
    public $decision;

    /**
     * @param array $jsonData
     * @return Response
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)  Deserializing a response to named DTOs
     * @SuppressWarnings(PHPMD.NPathComplexity)       A response has a lot of constraints
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength) Private methods would merely be code relocation
     */
    public static function fromData(array $jsonData)
    {
        if (!isset($jsonData['Response'])) {
            throw new InvalidPdpResponseException('Key: Response was not found in the PDP response');
        }

        if (!is_array($jsonData['Response'])) {
            throw new InvalidPdpResponseException('Key: Response is not an array');
        }


        if (!isset($jsonData['Response'][0])) {
            throw new InvalidPdpResponseException('No response data found');
        }

        $responseData = $jsonData['Response'][0];

        if (!isset($responseData['Status'])) {
            throw new InvalidPdpResponseException('Key: Status was not found in the PDP response');
        }

        if (!isset($responseData['PolicyIdentifier'])) {
            throw new InvalidPdpResponseException('Key: PolicyIdentifier was not found in the PDP response');
        }

        if (!isset($responseData['Decision'])) {
            throw new InvalidPdpResponseException('Key: Decision was not found in the PDP response');
        }

        $response = new self;

        $response->status = new Status;
        $response->status->statusCode = new StatusCode;
        $response->status->statusCode->value = $responseData['Status']['StatusCode']['Value'];
        if (isset($responseData['Status']['StatusDetail'])) {
            $response->status->statusDetail = $responseData['Status']['StatusDetail'];
        }
        if (isset($responseData['Status']['StatusMessage'])) {
            $response->status->statusMessage = $responseData['Status']['StatusMessage'];
        }

        if (isset($responseData['Category'])) {
            foreach ($responseData['Category'] as $categoryData) {
                $category             = new Category;
                $category->categoryId = $categoryData['CategoryId'];
                $category->attributes = [];

                foreach ($categoryData['Attribute'] as $attributeData) {
                    $attribute              = new Attribute;
                    $attribute->attributeId = $attributeData['AttributeId'];
                    $attribute->value       = $attributeData['Value'];

                    if (isset($attributeData['DataType'])) {
                        $attribute->dataType = $attributeData['DataType'];
                    }

                    $category->attributes[] = $attribute;
                }

                $response->categories[] = $category;
            }
        }

        if (isset($responseData['AssociatedAdvice'])) {
            foreach ($responseData['AssociatedAdvice'] as $associatedAdviceData) {
                $associatedAdvice = new AssociatedAdvice;
                $associatedAdvice->id = $associatedAdviceData['Id'];

                foreach ($associatedAdviceData['AttributeAssignment'] as $attributeAssignmentData) {
                    $attributeAssignment = new AttributeAssignment;
                    $attributeAssignment->category    = $attributeAssignmentData['Category'];
                    $attributeAssignment->attributeId = $attributeAssignmentData['AttributeId'];
                    $attributeAssignment->value       = $attributeAssignmentData['Value'];
                    if (isset($attributeAssignmentData['DataType'])) {
                        $attributeAssignment->dataType = $attributeAssignmentData['DataType'];
                    }
                    $associatedAdvice->attributeAssignments[] = $attributeAssignment;
                }

                $response->associatedAdvices[] = $associatedAdvice;
            }
        }

        $response->policyIdentifier = new PolicyIdentifier;

        if (isset($responseData['PolicyIdentifier']['PolicySetIdReference'])) {
            foreach ($responseData['PolicyIdentifier']['PolicySetIdReference'] as $policySetIdReferenceData) {
                $policySetIdReference                               = new PolicySetIdReference;
                $policySetIdReference->version                      = $policySetIdReferenceData['Version'];
                $policySetIdReference->id                           = $policySetIdReferenceData['Id'];
                $response->policyIdentifier->policySetIdReference[] = $policySetIdReference;
            }
        }

        if (isset($responseData['PolicyIdentifier']['PolicyIdReference'])) {
            foreach ($responseData['PolicyIdentifier']['PolicyIdReference'] as $policyIdReferenceData) {
                $policyIdReference                               = new PolicyIdReference;
                $policyIdReference->version                      = $policyIdReferenceData['Version'];
                $policyIdReference->id                           = $policyIdReferenceData['Id'];
                $response->policyIdentifier->policyIdReference[] = $policyIdReference;
            }
        }

        $response->decision = $responseData['Decision'];

        return $response;
    }
}
