<?php

class EngineBlock_Corto_Filter_Command_RejectProcessingMode extends EngineBlock_Corto_Filter_Command_Abstract
{
    public function execute()
    {
        if ($this->_server->isInProcessingMode()) {
            $this->stopFiltering();
        }
    }
}
