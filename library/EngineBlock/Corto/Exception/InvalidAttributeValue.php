<?php

/**
 * Exception thrown on attribute violation
 * This is thrown when validating the the assertion attributes on input filtering.
 */
class EngineBlock_Corto_Exception_InvalidAttributeValue extends EngineBlock_Exception implements EngineBlock_Corto_Exception_HasFeedbackInfoInterface
{
    private $attributeName;
    private $attributeValue;

    public function __construct($message, $attributeName, $attributeValue, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
        $this->attributeName = $attributeName;
        $this->attributeValue = $attributeValue;
    }

    public function getAttributeName()
    {
        return $this->attributeName;
    }

    public function getAttributeValue()
    {
        return $this->attributeValue;
    }

    /**
     * @return array
     */
    public function getFeedbackInfo()
    {
        return [
            'attributeName' => $this->attributeName,
            'attributeValue' => $this->attributeValue,
        ];
    }
}
