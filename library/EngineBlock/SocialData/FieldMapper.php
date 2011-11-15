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
 * Mapper class that makes a translation between OpenSocial and COIN fieldnames. 
 * @author ivo
 */
class EngineBlock_SocialData_FieldMapper
{
    /**
     * Mapping of COIN ldap keys to open social field names.
     * Some fields may map to multiple open social fields
     * e.g. both displayName and nickName in OpenSocial
     * are based on display name in COIN ldap.
     * @var array
     */
    protected $_l2oMap = array(
        "collabpersonid"            => "id" ,
        'collabpersonisguest'       => 'person.tags',
        'uid'                       =>  array(
                                        'account.username',
                                        'account.userId',
                                    ),
        "givenname"                 => "name.givenName",
        'sn'                        => 'name.familyName',
        'cn'                        => 'name.formatted',
        "displayname"               =>  array(
                                        "displayName",
                                        "nickname"
                                    ) ,
        "mail"                      => "emails",
        'o'                         => 'organizations.name',
        'schachomeorganizationtype' => 'organizations.type',
        'nledupersonorgunit'        => 'organizations.department',
        'edupersonaffiliation'      => 'organizations.title',
    );
    
    /**
     * Mapping of open social field names to COIN ldap keys
     * Must contain bare key/value pairs (doesn't support
     * multiple fields like _l2oMap.)
     * @var array
     */
    protected $_o2lMap = array(
        'id'                        => 'collabpersonid',
        'nickname'                  => 'displayname',
        'displayName'               => 'displayname',
        'emails'                    => 'mail',
        'account.username'          => 'uid',
        'name.familyName'           => 'sn',
        'name.givenName'            => 'givenname',
        'name.formatted'            => 'cn',
        'account.userId'            => 'uid',
        'organizations.name'        => 'o',
        'organizations.type'        => 'schachomeorganization',
        'organizations.department'  => 'schachomeorganizationtype',
        'organizations.title'       => 'edupersonaffiliation',
    );

    /**
     * A list of OpenSocial fields that are allowed to have multiple values.
     * @var array
     */
    protected $_oMultiValueAttributes = array(
        'emails'
    );

    /**
     * Returns a list of COIN ldap attributes based on a list of 
     * OpenSocial attributes.
     * 
     * If a social attribute is passed that has no COIN ldap counterpart,
     * it will not be converted and will be present in the output as-is.
     * 
     * Mind that the mapper is case sensitive.
     * @todo do we need this case sensitivity?
     * 
     * @param array $socialAttributes An array of OpenSocial attribute names
     * @return array The list of ldap attributes
     */
    public function socialToLdapAttributes($socialAttributes)
    {
        $result = array();
        foreach ($socialAttributes as $socialAttribute) {
            if (isset($this->_o2lMap[$socialAttribute])) {
                if (!in_array($this->_o2lMap[$socialAttribute], $result)) {
                    $result[] = $this->_o2lMap[$socialAttribute];
                }
            } else {
                // We have no mapping for this social field, use the key as-is
                // (useful if userregistry contains custom stuff)
                if (!in_array($socialAttribute, $result)) {
                    $result[] = $socialAttribute;
                }
            }
        }
        return $result;
    }

    /**
     * Convert a COIN ldap record to an OpenSocial record.
     * 
     * This method creates an OpenSocial record based on a COIN ldap record.
     * Mind you that the number of keys in the input and in the output might
     * be different, since the mapper can construct multiple OpenSocial
     * fields based on single values in coin ldap (eg displayname in ldap is
     * used for both displayname and nickname in OpenSocial.
     * 
     * The method has awareness of which fields in open social are single
     * and which are multivalue, and will make sure that in the return value
     * this is properly reflected.
     * 
     * It's possible to pass a list of socialAttrs you are interested in. If
     * this parameter is non-empty, only the social attributes present in the
     * array will be present in the output. (unknown keys will be silently
     * ignored). 
     *  
     * @param array $data The record to convert. Keys should be ldap 
     *                    attributes.
     * @param array $requestedAttributes The list of social attributes that you are
     *                     interested in. If omitted or empty array, will try
     *                     to get all fields.
     * @return array An array containing social key/value pairs                  
     */
    public function ldapToSocialData($data, $requestedAttributes = array())
    {
        $result = array();
        foreach ($data as $ldapAttribute => $value) {
            if (isset($this->_l2oMap[$ldapAttribute])) {
                $socialAttributes = (array)$this->_l2oMap[$ldapAttribute];
                foreach ($socialAttributes as $socialAttribute) {
                    if (!empty($requestedAttributes) && !in_array($socialAttribute, $requestedAttributes)) {
                        continue; // Did not request this attribute, skip it.
                    }

                    $converterMethod = 'convertLdap' . $ldapAttribute;
                    if (method_exists($this, $converterMethod)) {
                        $result = $this->$converterMethod($result, $value);
                    }
                    else {
                        $result = $this->_addMultiDimensionalOpenSocialAttribute(
                            $result,
                            $socialAttribute,
                            $this->_pack($value, $socialAttribute)
                        );
                    }
                }
            }
            else { // ignore value
            }
        }
        return $result;
    }

    /**
     * Stuff a given value for a possibly multi-dimensional (name.formatted) OpenSocial attribute
     * in a multi-dimensional array.
     *
     * Converts 'name.formatted' with value 'John Doe' into array('name'=>array('formatted'=>'John Doe'))
     *
     * @param  array  $result
     * @param  string $socialAttribute
     * @param  mixed  $value
     * @return array
     */
    protected function _addMultiDimensionalOpenSocialAttribute(array $result, $socialAttribute, $value)
    {
        $pointer = &$result;
        $parts = explode('.', $socialAttribute);
        while (!empty($parts)) {
            $part = array_shift($parts);
            if (!empty($parts)) {
                if (!isset($result[$part])) {
                    $result[$part] = array();
                }
                $pointer = &$result[$part];
            }
        }
        $pointer[$part] = $value;
        return $result;
    }

    /**
     * Converts a value to either an array or a single value, 
     * depending on whether the socialAttr passed is a multivalue
     * key.
     * @param mixed $value A single value or an array of values
     * @param String $socialAttributes The name of the social attribute that $value
     *                           is representing.
     * @return mixed
     */
    protected function _pack($value, $socialAttributes)
    {
        if (in_array($socialAttributes, $this->_oMultiValueAttributes)) {
            return (array)$value;
        } else {
            if (is_array($value)) {
                return array_shift($value);
            } else {
                return $value;
            }
        }
    }

    public function convertLdapCollabPersonIsGuest($result, $collabPersonIsGuest)
    {
        if ($collabPersonIsGuest === "FALSE") {
            $result['tags'][] = 'member';
        }
        else {
            $result['tags'][] = 'guest';
        }
        return $result;
    }
}
