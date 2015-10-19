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
        return ('permit' === strtolower(trim($this->Decision)));
    }
}
