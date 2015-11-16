<?php

class EngineBlock_PolicyDecisionPoint_PepValidator
{
    const DEFAULT_LANG = self::LANGUAGE_EN;
    const LANGUAGE_EN = 'en';

    /**
     * @var array<string,string>
     */
    private $messages;

    /**
     * @param string $subjectId
     * @param string $idp
     * @param string $sp
     * @param array $responseAttributes
     * @return bool
     */
    public function hasAccess($subjectId, $idp, $sp, array $responseAttributes)
    {
        $policy_request = $this->buildPolicyRequest($subjectId, $idp, $sp, $responseAttributes);

        $pdp = $this->createPdpClient($policy_request);
        $hasAccess = $pdp->hasAccess();

        if (!$hasAccess) {
            $this->messages = $pdp->getReason();
        }

        return $hasAccess;
    }

    /**
     * Get the response message when subject has no access.
     * 
     * @param string|null $lang
     * @return string|null
     */
    public function getMessage($lang = null)
    {
        $lang = !empty($lang) ? $lang : static::DEFAULT_LANG;

        if (isset($this->messages[$lang])) {
            return $this->messages[$lang];
        }

        return NULL;
    }

    /**
     * Build the policy request object.
     *
     * @param string $subjectId
     * @param string $idp
     * @param string $sp
     * @param array $responseAttributes
     * @return Pdp_PolicyRequest
     */
    private function buildPolicyRequest($subjectId, $idp, $sp, array $responseAttributes)
    {
        $policy_request = new Pdp_PolicyRequest();
        $policy_request->addResourceAttribute('SPentityID', $sp);
        $policy_request->addResourceAttribute('IDPentityID', $idp);

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
    protected function createPdpClient(Pdp_PolicyRequest $policy_request)
    {
        $conf = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        return new Pdp_Client($conf, $policy_request);
    }
}
