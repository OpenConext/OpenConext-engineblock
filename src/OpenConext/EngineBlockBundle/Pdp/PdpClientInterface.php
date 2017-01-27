<?php
namespace OpenConext\EngineBlockBundle\Pdp;

use OpenConext\EngineBlockBundle\Pdp\Dto\Request;

interface PdpClientInterface
{
    /**
     * @param Request $request
     * @return PolicyDecision $policyDecision
     */
    public function requestDecisionFor(Request $request);
}
