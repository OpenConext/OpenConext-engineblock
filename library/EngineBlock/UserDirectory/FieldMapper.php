<?php
class EngineBlock_UserDirectory_FieldMapper
{
    // Key: ldap, value: opensocial
    protected $_l2oMap = array(
        "collabpersonid" => "id" , 
        "displayname" => array(
            "displayname" , 
            "nickname"
        ) , "mail" => "emails" , 
        "givenname" => "name"
    );
    protected $_o2lMap = array(
        "id" => "collabpersonid" , 
        "nickname" => "displayname" , 
        "displayname" => "displayname" , 
        "emails" => "mail" , "name" => "givenname"
    );
    protected $_oMultiValueAttributes = array(
        'emails'
    );

    /**
     * 
     * @param $directoryItem
     */
    public function socialToLdapAttributes($attributes)
    {
        $result = array();
        foreach ($attributes as $socialAttr) {
            $socialAttr = strtolower($socialAttr);
            if (isset($this->_o2lMap[$socialAttr])) {
                $result[] = $this->_o2lMap[$socialAttr];
            } else {
                // We have no mapping for this social field, use the key as-is (useful if userregistry contains
                // custom stuff)
                $result[] = $socialAttr;
            }
        }
        return $result;
    }

    public function ldapToSocialData($data, $socialAttrs = array())
    {
        $result = array();
        if (count($socialAttrs)) {
            foreach ($socialAttrs as $socialAttr) {
                $socialAttr = strtolower($socialAttr);
                if (isset($this->_o2lMap[$socialAttr])) {
                    $ldapAttr = $this->_o2lMap[$socialAttr];
                    if (isset($data[$ldapAttr])) {
                        $result[$socialAttr] = $this->_pack($data[$ldapAttr], $socialAttr);
                    } else {    // if there's no opensocial equivalent for this field
                    // assume this is stuff we're not allowed to share
                    // so do not include it in the result.
                    }
                }
            }
        } else {
            foreach ($data as $ldapAttr => $value) {
                $ldapAttr = strtolower($ldapAttr);
                if (isset($this->_l2oMap[$ldapAttr])) {
                    if (is_array($this->_l2oMap[$ldapAttr])) {
                        foreach ($this->_l2oMap[$ldapAttr] as $socialAttr) {
                            $result[$socialAttr] = $this->_pack($value, $socialAttr);
                        }
                    } else {
                        $result[$this->_l2oMap[$ldapAttr]] = $this->_pack($value, $socialAttr);
                    }
                } else {    // ignore value
                }
            }
        }
        return $result;
    }

    protected function _pack($value, $socialAttr)
    {
        if (in_array($socialAttr, $this->_oMultiValueAttributes)) {
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