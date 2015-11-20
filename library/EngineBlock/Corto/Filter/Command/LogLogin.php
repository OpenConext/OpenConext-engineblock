<?php

class EngineBlock_Corto_Filter_Command_LogLogin extends EngineBlock_Corto_Filter_Command_Abstract
{
    public function execute()
    {
        if (!$this->_collabPersonId) {
            throw new EngineBlock_Corto_Filter_Command_Exception_PreconditionFailed(
                'Missing collabPersonId'
            );
        }

        $tracker = new EngineBlock_Tracker();
        $tracker->trackLogin(
            $this->_serviceProvider,
            $this->_identityProvider,
            $this->_collabPersonId,
            '',
            $this->_request->getKeyId()
        );
    }
}
