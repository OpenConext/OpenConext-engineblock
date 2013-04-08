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

class EngineBlock_Http_Response 
{
    const HTTP_HEADER_RESPONSE_LOCATION = 'Location';

    protected $_headers = array();
    protected $_body    = '';

    public function setHeader($name, $value)
    {
        $this->_headers[$name] = $value;
        return $this;
    }

    public function setRedirectUrl($url)
    {
        $this->_headers[self::HTTP_HEADER_RESPONSE_LOCATION] = $url;
        return $this;
    }

    public function getRedirectUrl()
    {
        if (isset($this->_headers[self::HTTP_HEADER_RESPONSE_LOCATION])) {
            return $this->_headers[self::HTTP_HEADER_RESPONSE_LOCATION];
        }
    }

    public function setBody($body)
    {
        $this->_body = $body;
    }

    public function send()
    {
        \Lvl\Profiler\Profiler::getInstance()->startBlock('send Response');

        foreach ($this->_headers as $headerName => $headerValue) {
            header("$headerName: $headerValue");
        }

        echo $this->_body;
    }

    /**
     * Set a cookie
     *
     * @param $name the cookie name
     * @param $value the cookie value
     * @param null $expire the expiration time
     * @param null $path the cookie path
     * @param null $domain the cookie domain
     * @param null $secure secure cookie?
     * @param null $httpOnly http only
     * @return bool true on success, false on failure
     */
    public function setCookie($name, $value, $expire = null, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        // @todo improve this
        // @workaround, do no write cookies in CLI environment for this causes output warnings in unit tests
        if (php_sapi_name() == 'cli') {
            return;
        }

        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }
}
