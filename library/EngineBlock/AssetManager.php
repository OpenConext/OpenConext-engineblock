<?php

class EngineBlock_AssetManager
{
    protected $_assetInfo;

    public function __construct()
    {
        $this->_assetInfo = json_decode(file_get_contents(ENGINEBLOCK_FOLDER_ROOT . 'www/authentication/assets.json'), true);
    }

    public function getCss()
    {
        return $this->getAssets('css');
    }

    public function getJs()
    {
        return $this->getAssets('js');
    }

    protected function _isDynamicEnvironment()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue('dynamicAssets', false);
    }

    protected function getAssets($type)
    {
        $assets = array();
        $env = $this->_isDynamicEnvironment() ? 'dynamic' : 'static';
        $files = $this->_assetInfo[$type][$env];
        foreach ($files as $asset) {
            $assets[] = ($type == 'css' ?
                "<link href='" . $asset . "' rel='stylesheet' type='text/css' />" :
                "<script type='text/javascript' src='" . $asset . "'></script>");
        }
        return implode($assets);
    }
}