<?php

/**
 * Called by Corto *after consent*, just as it prepares to send the response to the SP
 */
class EngineBlock_Corto_Filter_Output extends EngineBlock_Corto_Filter_Abstract
{
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
        return array(
            // If EngineBlock is in Processing mode (redirecting to it's self)
            // Then don't continue with the rest of the modifications
            new EngineBlock_Corto_Filter_Command_RejectProcessingMode(),

            // Check if the Policy Decision Point needs to be consulted for this request
            new EngineBlock_Corto_Filter_Command_PolicyDecisionPoint (),

            // Add collabPersonId attribute
            new EngineBlock_Corto_Filter_Command_AddCollabPersonIdAttribute(),

            // Run custom attribute manipulations
            new EngineBlock_Corto_Filter_Command_RunAttributeManipulations(
                EngineBlock_Corto_Filter_Command_RunAttributeManipulations::TYPE_SP
            ),

            // Run custom attribute manipulations in case we are behind a trusted proxy.
            new EngineBlock_Corto_Filter_Command_RunAttributeManipulations(
                EngineBlock_Corto_Filter_Command_RunAttributeManipulations::TYPE_REQUESTER_SP
            ),

            // Set the persistent Identifier for this user on this SP
            new EngineBlock_Corto_Filter_Command_SetNameId(),

            // Add the appropriate NameID to the 'eduPeronTargetedID'.
            new EngineBlock_Corto_Filter_Command_AddEduPersonTargettedId(),

            // Apply ARP to custom added attributes one last time for the eduPersonTargetedId
            new EngineBlock_Corto_Filter_Command_AttributeReleasePolicy(),

            // Convert all attributes to their OID format (if known) and add these.
            new EngineBlock_Corto_Filter_Command_DenormalizeAttributes(),

            // Log the login
            new EngineBlock_Corto_Filter_Command_LogLogin(),
        );
    }
}
