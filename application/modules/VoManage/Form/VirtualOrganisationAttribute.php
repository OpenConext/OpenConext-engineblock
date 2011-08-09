<?php
/**
 *
 */

class VoManage_Form_VirtualOrganisationAttribute extends Zend_Form
{
    public function init()
    {
        $this->setName('VirtualOrganisationAttribute')
                ->setMethod('post');

        $this->_initId()
                ->_initVOId()
                ->_initSpEntityId()
                ->_initUserIdPattern()
                ->_initAttributeNameSAML()
                ->_initAttributeNameOpenSocial()
                ->_initAttributeValue();
    }

    /**
     * @return VoManage_Form_VirtualOrganisationAttribute
     */
    public function _initId()
    {
        $element = new Zend_Form_Element_Hidden('id');
        return $this->addElement($element);
    }

    /**
     * @return VoManage_Form_VirtualOrganisationAttribute
     */
    public function _initVOId()
    {
        $element = new Zend_Form_Element_Text('vo_id');
        $element->setRequired(TRUE);
        $element->setAllowEmpty(false);
        $validator = new Zend_Validate_Regex("/^[a-zA-Z0-9\-_]+$/");
        $element->addValidator($validator);
        $element->addErrorMessage("Illegal characters detected.");
        return $this->addElement($element);
    }

    public function _initSpEntityId()
    {
        $element = new Zend_Form_Element_Text('sp_entity_id');
        $element->setRequired(true);
        $element->setAllowEmpty(false);
        return $this->addElement($element);
    }

    public function _initUserIdPattern()
    {
        $element = new Zend_Form_Element_Text('user_id_pattern');
        $element->setRequired(true);
        $element->setAllowEmpty(false);
        return $this->addElement($element);
    }

    public function _initAttributeNameSAML()
    {
        $element = new Zend_Form_Element_Text('attribute_name_saml');
        return $this->addElement($element);
    }

    public function _initAttributeNameOpenSocial()
    {
        $element = new Zend_Form_Element_Text('attribute_name_opensocial');
        return $this->addElement($element);
    }

    public function _initAttributeValue()
    {
        $element = new Zend_Form_Element_Text('attribute_value');
        $element->setRequired(true);
        $element->setAllowEmpty(false);
        return $this->addElement($element);
    }

    
}