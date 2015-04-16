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

        $voContext = null;
        if (isset($this->_responseAttributes[self::VO_NAME_ATTRIBUTE][0])) {
            $voContext = $this->_responseAttributes[self::VO_NAME_ATTRIBUTE][0];
        }

        $tracker = new EngineBlock_Tracker();
        $tracker->trackLogin(
            $this->_serviceProvider,
            $this->_identityProvider,
            $this->_collabPersonId,
            $voContext,
            $this->_request->getKeyId()
        );
    }
}