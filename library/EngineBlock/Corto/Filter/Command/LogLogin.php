<?php

class EngineBlock_Corto_Filter_Command_LogLogin extends EngineBlock_Corto_Filter_Command_Abstract
{
    const VO_NAME_ATTRIBUTE         = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.2';

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
            $this->_request->getKeyId()
        );
    }
}
