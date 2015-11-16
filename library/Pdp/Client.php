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
     * @var Pdp_PolicyResponse
     */
    protected $policyResponse;
    /**
     * @var Pdp_PolicyRequest
     */
    protected $policyRequest;

    public function __construct(Zend_Config $conf, Pdp_PolicyRequest $policyRequest)
    {
        if (empty($conf->pdp->baseUrl) OR
            empty($conf->pdp->username) OR
            empty($conf->pdp->password))
        {
            throw new EngineBlock_Exception('Invalid PDP client configuration. '
              . 'Please change the PDP section in the application.ini');
        }

        $this->baseUrl = $conf->pdp->baseUrl;
        $this->username = $conf->pdp->username;
        $this->password = $conf->pdp->password;

        $this->policyRequest = $policyRequest;
    }

    /**
     * Ask PDP for access.
     *
     * @return \Pdp_PolicyResponse
     * @throws \EngineBlock_Exception
     */
    protected function requestAccess()
    {
        $httpClient = new Zend_Http_Client($this->baseUrl);
        try {
            $result = $httpClient
                ->setConfig(array('timeout' => 15))
                ->setAuth($this->username, $this->password, Zend_Http_Client::AUTH_BASIC)
                ->setRawData($this->policyRequest->toJson())
                ->setEncType('application/json')
                ->request('POST');

            if ($result->getStatus() != '200') {

                $error = "Received invalid HTTP " .
                    $result->getStatus() .
                    "response from PDP";

                EngineBlock_ApplicationSingleton::getLog()->error($error);
                throw new EngineBlock_Exception($error);
            }
        }
        catch(Zend_Http_Client_Exception $e) {
            EngineBlock_ApplicationSingleton::getLog()
              ->error($e->getMessage());
            throw new EngineBlock_Exception($e->getMessage());
        }
        $this->policyResponse = new Pdp_PolicyResponse($result->getBody());
        return $this->policyResponse;
    }

    /**
     * Ask the PDP if the user has access to this service.
     */
    public function hasAccess()
    {
        return $this->requestAccess()->hasAccess();
    }

    /**
     * Return the status message from PDP.
     */
    public function getReason()
    {
        if ($this->policyResponse instanceof Pdp_PolicyResponse)
        {
            return $this->policyResponse->getMessage();
        }
        return NULL;
    }
}
