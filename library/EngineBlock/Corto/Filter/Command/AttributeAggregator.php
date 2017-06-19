<?php

use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AggregatedAttribute;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AttributeRule;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;
use OpenConext\EngineBlock\Http\Exception\HttpException;
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
     * @var AttributeAggregationClient
     */
    private $client;

    /**
     * @var MetadataRepositoryInterface
     */
    private $metadataRepository;

    /**
     * Metadata about the origin of response attributes.
     *
     * @var array
     */
    private $responseAttributeSources = [];

    /**
     * @param AttributeAggregationClient $client
     */
    public function __construct(
        LoggerInterface $logger,
        AttributeAggregationClientInterface $client,
        MetadataRepositoryInterface $metadataRepository
    ) {
        $this->logger = $logger;
        $this->client = $client;
        $this->metadataRepository = $metadataRepository;
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
            $this->_server->getRepository()
        );

        if (!$serviceProvider) {
            $serviceProvider = $this->_serviceProvider;
        }

        if (!$serviceProvider->attributeAggregationRequired) {
            $this->logger->info("No Attribute Aggregation for " . $serviceProvider->entityId);
            return;
        }

        $this->logger->notice("Attribute Aggregation for {$serviceProvider->entityId}");

        $rules = $this->createAttributeRulesForSp($serviceProvider);

        if (empty($rules)) {
            $this->logger->warning("No attribute rules configured for aggregation for " . $serviceProvider->entityId);
            return;
        }

        $this->clearAttributesForAggregation($rules);

        try {
            $response = $this->client->aggregate(
                Request::from(
                    $this->_collabPersonId,
                    (array) $this->_responseAttributes,
                    $rules
                )
            );

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
        $arp = $this->metadataRepository->fetchServiceProviderArp($sp);
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
