<?php

class EngineBlock_Corto_Filter_Command_AttributeAggregator extends EngineBlock_Corto_Filter_Command_Abstract
{
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

        $aggregator = $this->_getAggregator();

        $aggregations = $aggregator->aggregate(
            $this->_collabPersonId,
            $spEntityId,
            $this->_responseAttributes
        );

        $this->_responseAttributes = array_merge($this->_responseAttributes, $aggregations);
    }

    /**
     * @return EngineBlock_AttributeAggregation_Aggregator
     */
    protected function _getAggregator()
    {
        return new AttributeAggregation_Client(EngineBlock_ApplicationSingleton::getInstance()->getConfiguration());
    }
}
