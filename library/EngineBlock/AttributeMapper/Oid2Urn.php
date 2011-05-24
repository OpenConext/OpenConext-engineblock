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
 * @package   Attribute Mapper
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * See also: https://wiki.surfnetlabs.nl/display/coin2011/DEV-US0107
 */
class EngineBlock_AttributeMapper_Oid2Urn extends EngineBlock_AttributeMapper_Abstract
{
    protected $_mapping = array(
        'urn:mace:dir:attribute-def:eduPersonPrincipalName' => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.6', // Represented as a NameID element, OR as an attribute name/value pair
        'urn:mace:dir:attribute-def:sn'                     => 'urn:oid:2.5.4.4', // Surname
        'urn:mace:dir:attribute-def:givenName'              => 'urn:oid:2.5.4.42', // givenName
        'urn:mace:dir:attribute-def:displayName'            => 'urn:oid:2.16.840.1.113730.3.1.241', // displayName
        'urn:mace:dir:attribute-def:mail'                   => 'urn:oid:0.9.2342.19200300.100.1.3', //mail
        'urn:mace:terena.org:schac:homeOrganization'        => 'urn:oid:1.3.6.1.4.1.1466.115.121.1.15', //Domain name of the home organization
    );
}