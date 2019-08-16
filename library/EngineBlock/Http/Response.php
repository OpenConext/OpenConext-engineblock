<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class EngineBlock_Http_Response
{
    const HTTP_HEADER_RESPONSE_LOCATION = 'Location';

    protected $_statusCode = 200;
    protected $_statusMessage = 'Okay';
    protected $_headers = array();
    protected $_body    = '';

    public function setStatus($code, $message)
    {
        $this->_statusCode = $code;
        $this->_statusMessage = $message;
        return $this;
    }

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

    /**
     * @param $body
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    public function send()
    {
        header('HTTP/1.1 ' . $this->_statusCode . ' ' . $this->_statusMessage, true, $this->_statusCode);
        foreach ($this->_headers as $headerName => $headerValue) {
            header("$headerName: $headerValue");
        }

        echo $this->_body;
    }

    /**
     * Set a cookie
     *
     * @param string $name the cookie name
     * @param string $value the cookie value
     * @param int|null $expire the expiration time
     * @param string|null $path the cookie path
     * @param string|null $domain the cookie domain
     * @param bool|null $secure secure cookie?
     * @param bool|null $httpOnly http only
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
