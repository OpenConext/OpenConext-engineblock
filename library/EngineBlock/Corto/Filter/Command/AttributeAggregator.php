<?php

use OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AggregatedAttribute;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;

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

        $spEntityId = $serviceProvider->entityId;

        $logger->notice("Attribute Aggregation for $spEntityId");

        $response = $this->client->aggregate(
            Request::from(
                $this->_collabPersonId,
                (array) $this->_responseAttributes,
                []
            )
        );

        $this->replaceAggregatedAttributes($response->attributes);
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
