<?php

/**
 * Class Pdp_Client
 */
class Pdp_Client
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
    /**
     * @var Pdp_PolicyRequest
     */
    protected $policyRequest;

    public function __construct(Zend_Config $conf, Pdp_PolicyRequest $policyRequest)
    {
        $this->baseUrl = $conf->pdp->baseUrl;
        $this->username = $conf->pdp->username;
        $this->password = $conf->pdp->password;

        $this->policyRequest = $policyRequest;
    }
}

