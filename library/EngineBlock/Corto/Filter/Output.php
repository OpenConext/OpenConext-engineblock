<?php

/**
 * Called by Corto *after consent*, just as it prepares to send the response to the SP
 */
class EngineBlock_Corto_Filter_Output extends EngineBlock_Corto_Filter_Abstract
{
    public function filter()
    {
    }

    /**
     * These commands will be evaluated in order.
     *
     * A command can throw an exception and halt SSO,
     * it can manipulate the response or it's attributes or it can communicate with external systems.
     * One thing it can't do is communicate with the user.
     *
     * @return array
     */
    protected function _getCommands()
    {
        return array();
    }
}
