<?php

/**
 * Called by Corto before consent, right after it receives an Assertion with attributes from an Identity Provider.
 */
class EngineBlock_Corto_Filter_Input extends EngineBlock_Corto_Filter_Abstract
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
        $logger = $this->_server->getSystemLog();
        return array(
            // Show an error if we get responses that do not have the Success status code
            new EngineBlock_Corto_Filter_Command_ValidateSuccessfulResponse(),

            // Convert all OID attributes to URN and remove the OID variant
            new EngineBlock_Corto_Filter_Command_NormalizeAttributes(),

            // The IdP is not allowed to set the isMemberOf attribute with urn:collab:org groups
            // so we make sure to remove them
            new EngineBlock_Corto_Filter_Command_FilterReservedMemberOfValues(),

            // Run custom attribute manipulations
            new EngineBlock_Corto_Filter_Command_RunAttributeManipulations(
                EngineBlock_Corto_Filter_Command_RunAttributeManipulations::TYPE_IDP
            ),

            new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($logger),

            new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($logger),

            // Check whether this IdP is allowed to send a response to the destination SP
            new EngineBlock_Corto_Filter_Command_ValidateAllowedConnection(),

            // Require valid UID and SchacHomeOrganization
            new EngineBlock_Corto_Filter_Command_ValidateRequiredAttributes(),

            // Add guest status (isMemberOf)
            new EngineBlock_Corto_Filter_Command_AddGuestStatus(),

            // Provision the User to LDAP and figure out the collabPersonId
            new EngineBlock_Corto_Filter_Command_ProvisionUser(),

            // Apply the Attribute Release Policy before we do consent.
            new EngineBlock_Corto_Filter_Command_AttributeReleasePolicy(),
        );
    }
}
