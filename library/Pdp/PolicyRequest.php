<?php

/**
 * Class Pdp_PolicyRequest
 */
class Pdp_PolicyRequest
{
    protected $ReturnPolicyIdList = true;
    protected $CombinedDecision = false;
    protected $AccessSubject;
    protected $Resource;

    public function __construct()
    {
        // Initialize Resource.
        $this->Resource = new stdClass();
        $this->Resource->Attribute = array();

        // Initialize AccessSubject.
        $this->AccessSubject = new stdClass();
        $this->AccessSubject->Attribute = array();
    }

    /**
     * Return the policy request in json format.
     *
     * @return string
     */
    public function toJson()
    {
        // Mind that we export protected properties!
        $object = new stdClass();
        $object->ReturnPolicyIdList = $this->ReturnPolicyIdList;
        $object->CombinedDecision = $this->CombinedDecision;
        $object->AccessSubject = $this->AccessSubject;
        $object->Resource = $this->Resource;

        return json_encode($object);
    }

    public function addAccessSubject($attributeId, $value)
    {
        $attribute = $this->_getAttribute($attributeId, $value);
        array_push($this->AccessSubject->Attribute, $attribute);
    }

    /**
     * Add Resource attribute.
     *
     * @param $attributeId string
     * @param $value string
     */
    public function addResourceAttribute($attributeId, $value)
    {
        $attribute = $this->_getAttribute($attributeId, $value);
        array_push($this->Resource->Attribute, $attribute);
    }

    /**
     * @param string $attributeId
     * @param string $value
     *
     * @return \stdClass
     */
    private function _getAttribute($attributeId, $value)
    {
        $attribute = new stdClass();
        $attribute->AttributeId = $attributeId;
        $attribute->Value = $value;
        return $attribute;
    }
}
