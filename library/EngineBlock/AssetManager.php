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

class EngineBlock_AssetManager
{
    protected static $_assetInfo;

    protected static $_dynamicEnvironments = array("");//"dev", "test", "demo");

    public static function getCss()
    {
        return self::getAssets('css');
    }

    public static function getJs()
    {
        return self::getAssets('js');
    }

    protected static function _loadAssetInfo()
    {
        if (!isset(self::$_assetInfo)) {
            self::$_assetInfo = json_decode(file_get_contents(ENGINEBLOCK_FOLDER_ROOT . 'www/authentication/assets.json'), true);
        }
    }

    protected static function _isDynamicEnvironment()
    {
        $env = EngineBlock_ApplicationSingleton::getInstance()->getEnvironmentId();
        return in_array($env, self::$_dynamicEnvironments);
    }

    protected static function getAssets($type)
    {
        self::_loadAssetInfo();
        $assets = array();
        $env = self::_isDynamicEnvironment() ? 'dynamic' : 'static';
        $files = self::$_assetInfo[$type][$env];
        foreach ($files as $asset) {
            $assets[] = ($type == 'css' ?
                "<link href='" . $asset . "' rel='stylesheet' type='text/css' />" :
                "<script type='text/javascript' src='" . $asset . "'></script>");
        }
        return implode($assets);
    }


}