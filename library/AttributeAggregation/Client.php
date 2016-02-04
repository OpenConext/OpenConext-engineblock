<?php

/**
 * Class AttributeAggregation_Client
 */
class AttributeAggregation_Client
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    public function __construct(Zend_Config $conf)
    {
        if (empty($conf->attributeAggregation->baseUrl) OR
            empty($conf->attributeAggregation->username) OR
            empty($conf->attributeAggregation->password))
        {
            throw new EngineBlock_Exception('Invalid attributeAggregation client configuration. '
              . 'Please change the attributeAggregation section in the application.ini');
        }

        $this->baseUrl  = $conf->attributeAggregation->baseUrl;
        $this->username = $conf->attributeAggregation->username;
        $this->password = $conf->attributeAggregation->password;

    }

    /**
     * Get aggregations
     *
     * @param string $subjectId
     * @param string $spEntityId
     * @param array $responseAttributes
     * @return array $aggregations
     */
    public function aggregate($subjectId, $spEntityId, array $responseAttributes)
    {
        $request = new AttributeAggregation_Request($subjectId, $spEntityId, $responseAttributes);
        $rawJson = $this->postAggregation($request);
        $aggregations = json_decode($rawJson);
        if (!$aggregations) {
            throw new RuntimeException(
                "AttributeAggregation: Invalid JSON: " . $rawJson
            );
        }
        //transform the aggregations to responseAttributes format and merge with existing values if needed
        $attributes = array();
        foreach (array_values($aggregations) as $aggregation) {
            $name = $aggregation->name;
            $attributes[$name] = in_array($name, $responseAttributes) ?
                array_merge($responseAttributes[$name], $aggregation->values) : $aggregation->values;
        }
        return $attributes;
    }


    /**
     * Post request and return aggregations
     *
     * @param \AttributeAggregation_Request
     * @return string $rawJson
     * @throws \EngineBlock_Exception
     */
    private function postAggregation($attributeAggregationRequest)
    {
        $httpClient = new Zend_Http_Client($this->baseUrl);
        try {
            $result = $httpClient
                ->setConfig(array('timeout' => 15))
                ->setAuth($this->username, $this->password, Zend_Http_Client::AUTH_BASIC)
                ->setRawData($attributeAggregationRequest->toJson())
                ->setEncType('application/json')
                ->request('POST');

            if ($result->getStatus() != '200') {

                $error = "Received invalid HTTP " .
                    $result->getStatus() .
                    "response from Attribute Aggregator";

                EngineBlock_ApplicationSingleton::getLog()->error($error);
                throw new EngineBlock_Exception($error);
            }
        }
        catch(Zend_Http_Client_Exception $e) {
            EngineBlock_ApplicationSingleton::getLog()->error($e->getMessage());
            throw new EngineBlock_Exception($e->getMessage());
        }
        return $result->getBody();
    }

}
