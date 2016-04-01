<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

class EngineBlock_Tracker
{
    public function trackLogin(
        ServiceProvider $spEntityMetadata,
        IdentityProvider $idpEntityMetadata,
        $subjectId,
        $keyId
    ) {
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $logger = $diContainer->getAuthenticationLogger();
        $symfonyRequest = $diContainer->getSymfonyRequest();

        $authenticationContext = [
            'login_stamp' => time(),
            'user_id' => $subjectId,
            'sp_entity_id' => $spEntityMetadata->entityId,
            'idp_entity_id' => $idpEntityMetadata->entityId,
            'key_id' => $keyId,
            'user_agent' => $symfonyRequest->headers->get('User-Agent')
        ];

        $logger->info('login granted', $authenticationContext);
    }
}
