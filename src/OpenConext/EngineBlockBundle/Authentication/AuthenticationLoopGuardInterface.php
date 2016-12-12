<?php
namespace OpenConext\EngineBlockBundle\Authentication;

use OpenConext\Value\Saml\Entity;

interface AuthenticationLoopGuardInterface
{
    /**
     * @param Entity $serviceProvider
     * @param AuthenticationProcedureList $pastAuthenticationProcedures
     * @return
     */
    public function ensureNotStuckInLoop(
        Entity $serviceProvider,
        AuthenticationProcedureList $pastAuthenticationProcedures
    );
}
