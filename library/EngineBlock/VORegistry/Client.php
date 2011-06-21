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
 * Implementation of the Engine Block internal Virtual Organization 
 * Registry interface.
 * 
 * @author ivo
 */
class EngineBlock_VORegistry_Client
{
    /**
     * Returns an array with metadata about a Virtual Organisation
     * @param String $voIdentifier The identifier of the VO
     * @return array An array with 3 keys:
     *               - groupprovideridentifier: the identifier of the group 
     *                 directory system we need to query to find out the 
     *                 members of this VO, its groups etc.
     *               - groupidentifier: the identifier of the group in this 
     *                 group directory that contains the VO members
     *               - groupstem: if present, defines which stem in the group
     *                 directory to query. A dedicated group directory would 
     *                 not use a stem.
     */
    public function getGroupProviderMetadata($voIdentifier)
    {
        // @todo replace hardcoded values with actual lookup in VORegistry
        switch ($voIdentifier) {
            case "votest1":
                return array(
                    "groupidentifier"=>"votest1group",
                	"groupstem"=>"nl:votest1");
                break;

            case "managementvo":
                return array(
                    "groupidentifier"=>"managementvotest",
                    "groupstem"=>"nl:surfnet:management"
                );
                break;

            case "serviceregistryvo":
                return array(
                    "groupidentifier"=>"managementvotest",
                    "groupstem"=>"nl:surfnet:management"
                );
                break;

           default:
                return array(
                    "groupidentifier"=>"pci_members",
                    "groupstem"=>"nl:pci"
                );
                break;
        }
    }
}
