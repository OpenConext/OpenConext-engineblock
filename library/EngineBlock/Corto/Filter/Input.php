<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
    public function getCommands()
    {
        $diContainer          = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $featureConfiguration = $diContainer->getFeatureConfiguration();
        $logger               = EngineBlock_ApplicationSingleton::getLog();

        $blockUsersOnViolation = $featureConfiguration->isEnabled('eb.block_user_on_violation');
        $authnContextClassRefBlacklistPattern = $diContainer->getAuthnContextClassRefBlacklistRegex();

        $commands = array(
            // Validate if the authnContextClassRef is not blacklisted
            new EngineBlock_Corto_Filter_Command_ValidateAuthnContextClassRef($logger, $authnContextClassRefBlacklistPattern),

            // Validate if the MFA authnContextClassRef is valid when configured
            new EngineBlock_Corto_Filter_Command_ValidateMfaAuthnContextClassRef(),

            // Convert all OID attributes to URN and remove the OID variant
            new EngineBlock_Corto_Filter_Command_NormalizeAttributes(),

            // The IdP is not allowed to set the isMemberOf attribute with urn:collab:org groups
            // so we make sure to remove them
            new EngineBlock_Corto_Filter_Command_FilterReservedMemberOfValues(),

            // Run custom attribute manipulations
            new EngineBlock_Corto_Filter_Command_RunAttributeManipulations(
                EngineBlock_Corto_Filter_Command_RunAttributeManipulations::TYPE_IDP
            ),

            new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsSchacHomeOrganisation($logger, $blockUsersOnViolation),

            new EngineBlock_Corto_Filter_Command_VerifyShibMdScopingAllowsEduPersonPrincipalName($logger, $blockUsersOnViolation),

            // Check whether this IdP is allowed to send a response to the destination SP
            new EngineBlock_Corto_Filter_Command_ValidateAllowedConnection(),

            // Require valid UID and SchacHomeOrganization
            new EngineBlock_Corto_Filter_Command_ValidateRequiredAttributes(),

            // Add guest status (isMemberOf)
            new EngineBlock_Corto_Filter_Command_AddGuestStatus(),

            // Figure out the collabPersonId
            new EngineBlock_Corto_Filter_Command_ProvisionUser(),

            // Aggregate additional attributes for this Service Provider
            new EngineBlock_Corto_Filter_Command_AttributeAggregator(
                $logger,
                $diContainer->getAttributeAggregationClient()
            ),

            // Check if the Policy Decision Point needs to be consulted for this request
            new EngineBlock_Corto_Filter_Command_EnforcePolicy(),

            // Apply the Attribute Release Policy before we do consent.
            new EngineBlock_Corto_Filter_Command_AttributeReleasePolicy(),
        );

        if (!$featureConfiguration->isEnabled('eb.run_all_manipulations_prior_to_consent')) {
            return $commands;
        }

        $outputFilter = new EngineBlock_Corto_Filter_Output($this->_server);

        return array_merge($commands, $outputFilter->getCommands());
    }
}
