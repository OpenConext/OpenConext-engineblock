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

class EngineBlock_Http_Client_Adapter_Curl extends Zend_Http_Client_Adapter_Curl
{
    /**
     * Initialize curl
     *
     * @param  string  $host
     * @param  int     $port
     * @param  boolean $secure
     * @return void
     * @throws Zend_Http_Client_Adapter_Exception if unable to connect
     */
    public function connect($host, $port = 80, $secure = false)
    {
        parent::connect($host, $port, $secure);

        // for some reason the zend_http_client_adapter_curl only sets a timeout on
        // connecting, not on the reading.
        // So we override and set the same timeout being used for connecting as
        // the timeout for reading / writing.
        curl_setopt($this->_curl, CURLOPT_TIMEOUT, (int)$this->_config['timeout']);
    }
}