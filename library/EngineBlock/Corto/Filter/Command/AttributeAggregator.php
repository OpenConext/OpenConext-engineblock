<?php

use OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AggregatedAttribute;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AttributeRule;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

class EngineBlock_Corto_Filter_Command_AttributeAggregator extends EngineBlock_Corto_Filter_Command_Abstract
{
    /**
     * @var AttributeAggregationClient
     */
    private $client;

    /**
     * @param AttributeAggregationClient $client
     */
    public function __construct(AttributeAggregationClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        $logger = EngineBlock_ApplicationSingleton::getLog();

        $serviceProvider = EngineBlock_SamlHelper::findRequesterServiceProvider(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository()
        );

        if (!$serviceProvider) {
            $serviceProvider = $this->_serviceProvider;
        }

        if (!$serviceProvider->attributeAggregationRequired) {
            $logger->info("No Attribute Aggregation for " . $serviceProvider->entityId);
            return;
        }

        $logger->notice("Attribute Aggregation for {$serviceProvider->entityId}");

        $rules = $this->createAttributeRulesForSp($serviceProvider);

        if (empty($rules)) {
            $logger->warning("No attribute rules configured for aggregation for " . $serviceProvider->entityId);
            return;
        }

        $response = $this->client->aggregate(
            Request::from(
                $this->_collabPersonId,
                (array) $this->_responseAttributes,
                $rules
            )
        );

        $this->replaceAggregatedAttributes($response->attributes);
    }

    /**
     * Create attribute rule DTOs for the aggregator request.
     *
     * @param ServiceProvider $sp
     * @return AttributeRule[]
     */
    private function createAttributeRulesForSp(ServiceProvider $sp) {
        $arp = $this->getMetadataRepository()->fetchServiceProviderArp($sp);
        if (!$arp) {
            return [];
        }

        return AttributeRule::fromArp($arp);
    }

    /**
     * @return \OpenConext\Component\EngineBlockMetadata\MetadataRepository\CompositeMetadataRepository
     */
    private function getMetadataRepository()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
    }

    /**
     * Replace the attributes sent by the IdP with aggregated attributes.
     *
     * TODO: keep track of attribute sources.
     *
     * @param array $attributes
     */
    private function replaceAggregatedAttributes(array $attributes) {
        foreach ($attributes as $attribute) {
            /** @var AggregatedAttribute $attribute */
            $this->_responseAttributes[$attribute->name] = $attribute->values;
        }
    }
}
