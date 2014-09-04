<?php

abstract class Default_Controller_LoggedIn extends EngineBlock_Controller_Abstract
{
    /**
     * The attributes to filter from the federative attributes provided by SimpleSaml
     *
     * @var array
     */
    protected $ATTRIBUTES_FILTER = array(
        'urn:oid:2.5.4.42',
        'urn:oid:2.5.4.3',
        'urn:oid:2.5.4.4',
        'urn:oid:2.16.840.1.113730.3.1.241',
        'urn:oid:0.9.2342.19200300.100.1.1',
        'urn:oid:0.9.2342.19200300.100.1.3',
        'urn:oid:1.3.6.1.4.1.1466.115.121.1.15',
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.6',
        'coin:',
        'urn:nl.surfconext.licenseInfo',
        'urn:mace:dir:attribute-def:isMemberOf',
        'urn:oid:1.3.6.1.4.1.1076.20.40.40.1',
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.10'
    );

    public function init()
    {
        $this->user = $this->_initAuthentication();
        $this->_getAttributes();
    }

    /**
     * Set the federative attributes that have been passed through by simplesaml
     * However, filter them according to the specified filter
     *
     * @return void
     */
    protected function _getAttributes()
    {
        $attributes = $this->user->getAttributes();

        foreach ($attributes as $attributeId => $attributeValue) {
            if ($this->_isInFilter($attributeId)) {
                unset($attributes[$attributeId]);
            }
        }

        $this->attributes = $attributes;
    }

    /**
     * Does this attributeId exist in the filter array?
     *
     * @param $attributeId
     * @return bool true if it exists in the filter, false if it doesn't
     */
    protected function _isInFilter($attributeId) {
        foreach ($this->ATTRIBUTES_FILTER as $filter) {
            if (strstr($attributeId, $filter)) {
                return true;
            }
        }
        return false;
    }
}
