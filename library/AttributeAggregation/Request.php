<?php

/**
 * Class AttributeAggregation_Request
 */
class AttributeAggregation_Request
{
    protected $request;
    /*
     * @param string $subjectId
     * @param string $spEntityId
     * @param array $responseAttributes
     */
    public function __construct($subjectId, $spEntityId, array $responseAttributes)
    {
        $this->request = new stdClass();
        $this->request->serviceProviderEntityId = $spEntityId;

        $attributes = array();
        $attributes[] = array(
            "name" => "urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified",
            "values" => array($subjectId)
        );

        foreach ($responseAttributes as $attributeName => $attributeValues) {
            $attributes[] = array("name" => $attributeName, "values" => $attributeValues);
        }

        $this->request->attributes = $attributes;
    }

    /**
     * Return the attribute aggregation request in json format.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->request);
    }

}
