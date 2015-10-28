<?php

class EngineBlock_PolicyDecisionPoint_PEPValidator
{
    private $message;
    private $lang = 'en';

    /**
     * @param $subjectId
     * @param $idp
     * @param $sp
     * @param $responseAttributes
     * @return bool
     */
    public function hasAccess($subjectId, $idp, $sp, $responseAttributes)
    {
        $policy_request = $this->buildPolicyRequest($subjectId, $idp, $sp, $responseAttributes);
        $pdp = $this->policyDecisionPoint($policy_request);
        $hasAccess = $pdp->hasAccess();

        if (!$hasAccess)
        {
            $this->message = $pdp->getReason();
        }

        return $hasAccess;
    }

    /**
     * @param string $lang
     *
     * @return EngineBlock_PolicyDecisionPoint_PEPValidator
     */
    public function setLang($lang) {
        $this->lang = $lang;
        return $this;
    }

    /**
     * Get the response message when subject has no access.
     */
    public function getMessage()
    {
        if (isset($this->message[$this->lang]))
        {
            return $this->message[$this->lang];
        }
        return NULL;
    }

    /**
     * Build the policy request object.
     * @param string $subjectId
     * @param string $idp
     * @param string $sp
     * @param array $responseAttributes
     * @return Pdp_PolicyRequest
     */
    private function buildPolicyRequest($subjectId, $idp, $sp, $responseAttributes)
    {
        $policy_request = new Pdp_PolicyRequest();
        $policy_request->addResourceAttribute("SPentityID", $sp);
        $policy_request->addResourceAttribute("IDPentityID", $idp);

        $policy_request->addAccessSubject('urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified', $subjectId);

        foreach ($responseAttributes as $id => $values) {
            foreach ($values as $value) {
                $policy_request->addAccessSubject($id, $value);
            }
        }

        return $policy_request;
    }

    /**
     * The PDP client.
     *
     * @param Pdp_PolicyRequest $policy_request
     *
     * @return Pdp_Client
     */
    protected function policyDecisionPoint(Pdp_PolicyRequest $policy_request = NULL)
    {
        $conf = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        return new Pdp_Client($conf, $policy_request);
    }
}
