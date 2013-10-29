<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
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
    protected function _getCommands()
    {
        return array(
            // Show an error if we get responses that do not have the Success status code
            new EngineBlock_Corto_Filter_Command_ValidateSuccessfulResponse(),

            // Convert all OID attributes to URN and remove the OID variant
            new EngineBlock_Corto_Filter_Command_NormalizeAttributes(),

            // The IdP is not allowed to set the isMemberOf attribute with VO groups
            // so we make sure to remove them
            new EngineBlock_Corto_Filter_Command_RemoveVoGroups(),

            // Run custom attribute manipulations
            new EngineBlock_Corto_Filter_Command_RunAttributeManipulations(
                EngineBlock_Corto_Filter_Command_RunAttributeManipulations::TYPE_IDP
            ),

            // Check whether this IdP is allowed to send a response to the destination SP
            new EngineBlock_Corto_Filter_Command_ValidateAllowedConnection(),

            // Require valid UID and SchacHomeOrganization
            new EngineBlock_Corto_Filter_Command_ValidateRequiredAttributes(),

            // Add guest status (isMemberOf)
            new EngineBlock_Corto_Filter_Command_AddGuestStatus(),

            // Provision the User to LDAP and figure out the collabPersonId
            new EngineBlock_Corto_Filter_Command_ProvisionUser(),
        );
    }
}