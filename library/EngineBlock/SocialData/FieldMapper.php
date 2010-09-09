<?php

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
 * schacHomeOrganization:uid 	 id
uid 	account.username
sn 	name.familyName
givenName 	name.givenName
cn 	name.formatted
uid 	account.userId
displayName 	displayName
mail 	emails
preferredLanguage
schacHomeOrganization 	organizations.name
schacHomeOrganizationType 	organizations.type
nlEduPersonOrgUnit 	organizations.department
eduPersonAffiliation 	organizations.title
 */
    
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
     * Mapping of OpenSocial fieldnames to Grouper field names.
     *
     * @var array
     */
    protected $_o2gMap = array(
        'id'    => 'name',
        'title' => 'displayExtension',
        'description' => 'description',
    );

    /**
     * Mapping of Grouper field names to OpenSocial keys.
     *
     * @var array
     */
    protected $_g2oMap = array(
        'name'              => 'id',
        'displayExtension'  => 'title',
        'description'       => 'description',
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
                // We have no mapping for this social field, use the key as-is (useful if userregistry contains
                // custom stuff)
                if (!in_array($socialAttribute, $result)) {
                    $result[] = $socialAttribute;
                }
            }
        }
        return $result;
    }

    /**
     * Convert a COIN ldap record to an opensocial record.
     * 
     * This method creates an opensocial record based on a COIN ldap record.
     * Mind you that the number of keys in the input and in the output might
     * be different, since the mapper can construct multiple opensocial
     * fields based on single values in coin ldap (eg displayname in ldap is
     * used for both displayname and nickname in opensocial.
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
     * @param array $socialAttributes The list of social attributes that you are
     *                     interested in. If omited or empty array, will try
     *                     to get all fields.
     * @return array An array containing social key/value pairs                  
     */
    public function ldapToSocialData($data, $socialAttributes = array())
    {
        $result = array();
        if (count($socialAttributes)) {
            foreach ($socialAttributes as $socialAttribute) {
                if (isset($this->_o2lMap[$socialAttribute])) {
                    $ldapAttribute = $this->_o2lMap[$socialAttribute];
                    if (isset($data[$ldapAttribute])) {
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
                        $pointer[$part] = $this->_pack($data[$ldapAttribute], $socialAttribute);
                    } else {    // if there's no opensocial equivalent for this field
                    // assume this is stuff we're not allowed to share
                    // so do not include it in the result.
                    }
                }
            }
        } else {
            foreach ($data as $ldapAttribute => $value) {
                if (isset($this->_l2oMap[$ldapAttribute])) {
                    if (is_array($this->_l2oMap[$ldapAttribute])) {
                        foreach ($this->_l2oMap[$ldapAttribute] as $socialAttribute) {
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
                            $pointer[$part] = $this->_pack($value, $socialAttribute);
                        }
                    } else {
                        $socialAttribute = $this->_l2oMap[$ldapAttribute];
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
                        $pointer[$part] = $this->_pack($value, $socialAttribute);
                    }
                } else {    // ignore value
                }
            }
        }
        return $result;
    }

    /**
     * Convert a Grouper (group) array to an OpenSocial array.
     *
     * @param  $group Group record
     * @return array OpenSocial record
     */
    public function grouperToSocialData($group)
    {
        $result = array();
        foreach ($group as $grouperAttribute => $value) {
            if (isset($this->_g2oMap[$grouperAttribute])) {
                $openSocialKey = $this->_g2oMap[$grouperAttribute];
                $result[$openSocialKey] = $value;
            }
            else {
                // Ignore values not present in the mapping
            }
        }
        return $result;
    }

    /**
     * Converts a value to either an array or a single value, 
     * depending on whether the socialAttr passed is a multivalue
     * key.
     * @param mixed $value A single value or an array of values
     * @param String $socialAttributes The name of the social attribute that $value
     *                           is representing.
     */
    protected function _pack($value, $socialAttributes)
    {
        if (in_array($socialAttributes, $this->_oMultiValueAttributes)) {
            if (is_array($value)) {
                return $value;
            } else {
                return array(
                    
                    $value
                );
            }
        } else {
            if (is_array($value)) {
                return $value[0];
            } else {
                return $value;
            }
        }
    }
}
