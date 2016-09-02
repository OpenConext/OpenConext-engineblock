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
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();

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

            // Aggregate additional attributes for this Service Provider
            new EngineBlock_Corto_Filter_Command_AttributeAggregator(),

            // Check if the Policy Decision Point needs to be consulted for this request
            new EngineBlock_Corto_Filter_Command_EnforcePolicy(),

            // Apply the Attribute Release Policy before we do consent.
            new EngineBlock_Corto_Filter_Command_AttributeReleasePolicy(),

            // The following actions were previously in the EngineBlock_Corto_Filter_Output
            // If EngineBlock is in Processing mode (redirecting to it's self)
            // Then don't continue with the rest of the modifications
            new EngineBlock_Corto_Filter_Command_RejectProcessingMode(),

            // Check if the request was for a VO, if it was, validate that the user is a member of that vo
            new EngineBlock_Corto_Filter_Command_ValidateVoMembership(),

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
            new EngineBlock_Corto_Filter_Command_LogLogin($diContainer->getAuthenticationLogger()),
        );
    }
}
