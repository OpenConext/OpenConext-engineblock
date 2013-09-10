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

define('ENGINEBLOCK_FOLDER_SHINDIG', ENGINEBLOCK_FOLDER_VENDOR . 'apache/shindig/');

class Social_Controller_Rest extends EngineBlock_Controller_Abstract
{
    public function indexAction($url)
    {
        $this->setNoRender(); // let shindig do the rendering

        require_once(ENGINEBLOCK_FOLDER_SHINDIG . 'src/common/Config.php');
        require_once(ENGINEBLOCK_FOLDER_SHINDIG . 'src/common/File.php');

        // You can't inject a Config, so force it to try loading
        // and ignore errors from config file not being there :(
        global $shindigConfig;
        $shindigConfig = array();
        @Config::setConfig(array('allow_plaintext_token'=>true,
                                 'person_service'       => 'EngineBlock_Shindig_DataService',
                                 'activity_service'     => 'EngineBlock_Shindig_DataService',
                                 'group_service'        => 'EngineBlock_Shindig_DataService',
        ));

        // Shindig expects urls to be mounted on /social/rest so we enforce that.
        $_SERVER['REQUEST_URI'] = '/social/rest/' . $url;
        // We only support JSON
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        
        // Shindig wants a security token, but interface F in coin is auth-less so we fake one.
        $_REQUEST["st"] = $_GET["st"] = $_POST["st"] = "o:v:a:d:u:m:c";

        $requestMethod = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getMethod();
        $methodName = 'do' . ucfirst(strtolower($requestMethod));

        $servletInstance = new DataServiceServlet();
        if (is_callable(array($servletInstance, $methodName))) {
            $servletInstance->$methodName();
        }
        else {
            echo "Invalid method";
            // @todo Error out
        }
    }
}
