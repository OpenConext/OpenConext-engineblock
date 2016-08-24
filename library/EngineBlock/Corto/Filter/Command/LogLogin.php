<?php

use OpenConext\EngineBlockBridge\Logger\AuthenticationLoggerAdapter;

class EngineBlock_Corto_Filter_Command_LogLogin extends EngineBlock_Corto_Filter_Command_Abstract
{
    /**
     * @var AuthenticationLoggerAdapter
     */
    private $authenticationLogger;

    public function __construct(AuthenticationLoggerAdapter $authenticationLogger)
    {
        $this->authenticationLogger = $authenticationLogger;
    }

    public function execute()
    {
        if (!$this->_collabPersonId) {
            throw new EngineBlock_Corto_Filter_Command_Exception_PreconditionFailed(
                'Missing collabPersonId'
            );
        }

        $this->authenticationLogger->logLogin(
            $this->_serviceProvider,
            $this->_identityProvider,
            $this->_collabPersonId,
            $this->_request->getKeyId()
        );
    }
}
