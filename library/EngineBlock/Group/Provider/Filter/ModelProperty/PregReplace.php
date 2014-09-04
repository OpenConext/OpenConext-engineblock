<?php

class EngineBlock_Group_Provider_Filter_ModelProperty_PregReplace implements EngineBlock_Group_Provider_Filter_Interface
{
    protected $_options;

    public function __construct(Zend_Config $options)
    {
        $this->_options = $options;
    }

    public function filter($data)
    {
        $modelProperties = array_keys(get_object_vars($data));
        foreach ($modelProperties as $modelProperty) {
            if (isset($this->_options->property) && $modelProperty !== $this->_options->property) {
                continue;
            }

            $data->$modelProperty = preg_replace($this->_options->search, $this->_options->replace, $data->$modelProperty);
        }
        return $data;
    }
}