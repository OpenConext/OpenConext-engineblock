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

            // Check if the request was for a VO, if it was, validate that the user is a member of that vo
            new EngineBlock_Corto_Filter_Command_ValidateVoMembership(),

            // Add collabPersonId attribute
            new EngineBlock_Corto_Filter_Command_AddCollabPersonIdAttribute(),

            // Apply ARP before we add the OID variants
            new EngineBlock_Corto_Filter_Command_AttributeReleasePolicy(),

            // Run custom attribute manipulations
            new EngineBlock_Corto_Filter_Command_RunAttributeManipulations(
                EngineBlock_Corto_Filter_Command_RunAttributeManipulations::TYPE_SP
            ),

            // Set the persistent Identifier for this user on this SP
            new EngineBlock_Corto_Filter_Command_SetNameId(),

            // Apply ARP to custom added attributes one last time for the eduPersonTargetedId
            new EngineBlock_Corto_Filter_Command_AttributeReleasePolicy(),

            // Convert all attributes to their OID format (if known) and add these.
            new EngineBlock_Corto_Filter_Command_DenormalizeAttributes(),

            // Log the login
            new EngineBlock_Corto_Filter_Command_LogLogin(),
        );
    }
}
