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

use OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AggregatedAttribute;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AttributeRule;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;
use OpenConext\EngineBlock\Http\Exception\HttpException;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use Psr\Log\LoggerInterface;


class EngineBlock_Corto_Filter_Command_AttributeAggregator extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface,
    EngineBlock_Corto_Filter_Command_ResponseAttributeSourcesModificationInterface,
    EngineBlock_Corto_Filter_Command_ResponseModificationInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AttributeAggregationClientInterface
     */
    private $client;

    /**
     * Metadata about the origin of response attributes.
     *
     * @var array
     */
    private $responseAttributeSources = [];

    /**
     * @param AttributeAggregationClientInterface $client
     */
    public function __construct(
        LoggerInterface $logger,
        AttributeAggregationClientInterface $client
    ) {
        $this->logger = $logger;
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * {@inhericdoc}
     */
    public function getResponseAttributes()
    {
        return (array) $this->_responseAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseAttributeSources()
    {
        return $this->responseAttributeSources;
    }

    public function execute()
    {
        $serviceProvider = EngineBlock_SamlHelper::findRequesterServiceProvider(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository(),
            $this->logger
        );

        if (!$serviceProvider) {
            $serviceProvider = $this->_serviceProvider;
        }

        if (!$serviceProvider->isAttributeAggregationRequired()) {
            $this->logger->info("No Attribute Aggregation for " . $serviceProvider->entityId);
            return;
        }

        $this->logger->notice("Attribute Aggregation for {$serviceProvider->entityId}");

        $rules = $this->createAttributeRulesForSp($serviceProvider);

        if (empty($rules)) {
            $this->logger->warning("No attribute rules configured for aggregation for " . $serviceProvider->entityId);
            return;
        }

        // The AA request contains all response attributes.
        $request = Request::from(
            $serviceProvider->entityId,
            $this->_collabPersonId,
            (array) $this->_responseAttributes,
            $rules
        );

        $this->clearAttributesForAggregation($rules);

        try {
            $response = $this->client->aggregate($request);

            $this->addAggregatedAttributes($response->attributes);
        } catch (HttpException $e) {
            $this->logger->error(
                "Error accessing the attribute aggregator API endpoint for {$serviceProvider->entityId}",
                [
                    'exception' => $e,
                ]
            );
        }
    }

    /**
     * Create attribute rule DTOs for the aggregator request.
     *
     * @param ServiceProvider $sp
     * @return AttributeRule[]
     */
    private function createAttributeRulesForSp(ServiceProvider $sp) {
        $arp = $sp->getAttributeReleasePolicy();
        if (!$arp) {
            return [];
        }

        return AttributeRule::fromArp($arp);
    }

    /**
     * Clear all response attributes configured for aggregation.
     *
     * Attributes defined for aggregation must always come from the
     * aggregator, so before adding the aggregated attributes we clear all
     * attributes that do not come from the aggregator but are configured for
     * aggregation.
     *
     * @param AttributeRule[] $rules
     */
    private function clearAttributesForAggregation(array $rules) {
        foreach ($rules as $rule) {
            unset($this->_responseAttributes[$rule->name]);
        }
    }

    /**
     * Replace the attributes sent by the IdP with aggregated attributes.
     *
     * @param AggregatedAttribute[] $attributes
     */
    private function addAggregatedAttributes(array $attributes) {
        foreach ($attributes as $attribute) {
            $this->_responseAttributes[$attribute->name] = $attribute->values;
            $this->responseAttributeSources[$attribute->name] = $attribute->source;
        }
    }
}
