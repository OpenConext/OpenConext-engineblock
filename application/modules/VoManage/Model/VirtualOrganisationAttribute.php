<?php

class VoManage_Model_VirtualOrganisationAttribute
{
    /**
     * row identifier
     *
     * @var String
     */
    public $id;

    /**
     * VO identifier
     *
     * @var String
     */
    public $vo_id;

    /**
     *
     * @var String
     */
    public $sp_entity_id;

    /**
     *
     * @var String
     */
    public $user_id_pattern;

    /**
     *
     * @var String
     */
    public $attribute_name_saml;

    /**
     *
     * @var String
     */
    public $attribute_name_opensocial;

    /**
     *
     * @var String
     */
    public $attribute_value;

    
    /* Default Model stuff */
    
    public $errors = array();

    public function toArray()
    {
        $ret = array();
        foreach ($this as $propertyName => $propertyValue) {
            if ($propertyName == 'attribute_value') {
                $decoded = json_decode($propertyValue);
                $ret[$propertyName] = ($decoded != null ? $decoded : $propertyValue);
            }
            $ret[$propertyName] = $propertyValue;
        }
        return $ret;
    }

    public function populate(array $row)
    {
        foreach ($row as $key=>$value) {
            if (property_exists($this, $key)) {
                if ($key == 'attribute_value' && is_array($value)) {
                    $this->$key = json_encode($value);
                } else $this->$key = $value;
            }
        }
        return $this;
    }

}
