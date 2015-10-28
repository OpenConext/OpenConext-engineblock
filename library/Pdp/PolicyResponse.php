<?php

/**
 * Class Pdp_PolicyRequest
 */
class Pdp_PolicyResponse
{
    protected $Status;
    protected $Decision;
    protected $PolicyIdentifier;
    protected $AssociatedAdvice;
    protected $rawJsonResponse;

    /**
     * Initialize from json response body.
     */
    public function __construct($rawJsonResponse)
    {
        $this->rawJsonResponse = $rawJsonResponse;
        $json_object = json_decode($rawJsonResponse);
        $response = $json_object->Response[0];

        $this->Status           = $response->Status;
        $this->Decision         = $response->Decision;
        $this->PolicyIdentifier = $response->PolicyIdentifier;

        if (!empty($response->AssociatedAdvice))
        {
            $this->AssociatedAdvice = $response->AssociatedAdvice;
        }
    }

    /**
     * Do we have access?
     * @return bool
     */
    public function hasAccess()
    {
        $decision = strtolower(trim($this->Decision));
        return ('permit' === $decision || 'notapplicable' === $decision);
    }

    /**
     * Status message from PDP
     *
     * Format:
     * array( language => message);
     * Example:
     *   array( 'en' => 'Not authorized' );
     */
    public function getMessage()
    {
        $message = NULL;

        switch (strtolower(trim($this->Decision)))
        {
            case "deny":
                $message = $this->getAssociatedAdvice();
                break;
            case "indeterminate":
                $message = array('en' => $this->Status->StatusMessage);
                break;
        }
        return $message;
    }

    /**
     * Return message (multi language) from PDP.
     */
    private function getAssociatedAdvice()
    {
        $advice = array();
        foreach ($this->AssociatedAdvice as $AssociatedAdvice)
        {
            foreach ($AssociatedAdvice->AttributeAssignment as $AttributeAssignment)
            {
                $lang = $this->getLanguageFromAttributeId($AttributeAssignment->AttributeId);
                $advice[$lang] = $AttributeAssignment->Value;
            }
        }
        return $advice;
    }

    /**
     * Returns the language of the message.
     */
    private function getLanguageFromAttributeId($id)
    {
        $id_array = explode(':', $id, 2);
        return array_pop($id_array);
    }
}
