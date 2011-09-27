<?php
 
abstract class Corto_Package_Abstract
{
    protected $_configs = array();
    protected $_metaData = array('hosted'=>array(),'remote'=>array());
    protected $_templateContents = array();

    public function setConfiguration(array $configs)
    {
        $this->_configs = $configs;
        return $this;
    }

    public function setRemoteMetaData(array $entities)
    {
        $this->_metaData['remote'] = $entities;
        return $this;
    }

    public function setHostedMetaData(array $entities)
    {
        $this->_metaData['hosted'] = $entities;
        return $this;
    }

    public function setTemplateContents($templateName, $templateContent)
    {
        $this->_templateContents[$templateName] = $templateContent;
        return $this;
    }

    abstract public function build($filePath); 
}
