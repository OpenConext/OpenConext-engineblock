<?php
namespace OpenConext\EngineBlockBundle\Authentication;

use OpenConext\Value\Saml\Entity;

interface AuthenticationLoopGuardInterface
{
    /**
     * @param Entity $serviceProvider
     * @param AuthenticationProcedureMap $pastAuthenticationProcedures
     * @return
     */
    public function detectsAuthenticationLoop(
        Entity $serviceProvider,
        AuthenticationProcedureMap $pastAuthenticationProcedures
    );
}
