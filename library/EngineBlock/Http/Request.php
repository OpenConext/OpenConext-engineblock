<?php

/**
 * Copyright 2010 SURFnet B.V.
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

class EngineBlock_Http_Request
{
    protected $_method;
    protected $_httpProtocol;

    protected $_protocol;
    protected $_hostName;
    protected $_uri;

    protected $_queryParameters = array();
    protected $_queryString;

    protected $_postParameters;

    protected $_headers;
    protected $_rawBody;

    public static function createFromEnvironment()
    {
        $request = new self();

        $request->setProtocol((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on'));
        $request->setMethod($_SERVER['REQUEST_METHOD'] ?? null);
        $request->setHttpProtocol($_SERVER['SERVER_PROTOCOL'] ?? null);

        $queryStart = strpos($_SERVER['REQUEST_URI'] ?? null, '?');
        if ($queryStart !== false) {
            $request->setUri(substr($_SERVER['REQUEST_URI'] ?? null, 0, $queryStart));
        }
        else {
            $request->setUri($_SERVER['REQUEST_URI'] ?? null);
        }
        $request->setQueryString($_SERVER['QUERY_STRING'] ?? null);
        $request->_setPostParameters($_POST);

        $headers = array();
        foreach ($_SERVER as $name => $value) {
           if (substr($name, 0, 5) !== 'HTTP_') {
               continue;
           }

           $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
           $headers[$headerName] = $value;
        }
        $request->setHeaders($headers);
        return $request;
    }

    public function setMethod($method)
    {
        $this->_method = $method;
        return $this;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param string $httpProtocol
     * @return EngineBlock_Http_Request
     */
    public function setHttpProtocol($httpProtocol)
    {
        $this->_httpProtocol = $httpProtocol;
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpProtocol()
    {
        return $this->_httpProtocol;
    }

    public function setUri($uri)
    {
        $this->_uri = urldecode($uri);
        return $this;
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function setQueryString($queryString)
    {
        $this->_queryString = $queryString;
        $this->_setQueryParameters($queryString);
        return $this;
    }

    public function getQueryString()
    {
        return $this->_queryString;
    }

    public function getQueryParameter($name)
    {
        if (isset($this->_queryParameters[$name])) {
            return $this->_queryParameters[$name];
        }
        return null;
    }

    public function getQueryParameters()
    {
        return $this->_queryParameters;
    }

    protected function _setQueryParameters($queryString)
    {
        $this->_queryParameters = array();
        $queryParts = explode("&", $queryString);
        foreach($queryParts as $queryPart) {
            if (preg_match("/^(.+)=(.*)$/", $queryPart, $keyAndValue))  {
                $key    = $keyAndValue[1];
                $value  = urldecode($keyAndValue[2]);

                $this->_queryParameters[$key] = $value;
            }
        }
    }

    protected function _setPostParameters($params)
    {
        $this->_postParameters = $params;
    }

    /**
     * Get all the POST parameters for the given request
     *
     * @return array all POST parameters as an array for the given request
     */
    public function getPostParameters()
    {
        return $this->_postParameters;
    }

    /**
     * Get the value of a specific POST variable based on a given parameter name
     *
     * @param string $param the name of the post parameter
     * @return mixed|null the value of the requested post parameter || null when it doesn't exist
     */
    public function getPostParameter($param)
    {
        if (array_key_exists($param, $this->_postParameters)) {
            return $this->_postParameters[$param];
        }
        return null;
    }

    /**
     * Get the raw post body.
     *
     * @return string
     */
    public function getRawBody()
    {
        // php://input can be read from only once until PHP 5.6. By storing the data in a temporary stream, we can read
        // from it as many times as we like.
        if ($this->_rawBody === null) {
            $this->_rawBody = fopen('php://temp', 'w+');
            $input = fopen('php://input', 'r');
            if ($input !== false && $this->_rawBody !== false) {
                stream_copy_to_stream($input, $this->_rawBody);
                fclose($input);
            } // should probably throw an exception
        }

        return stream_get_contents($this->_rawBody, -1, 0);
    }

    /**
     * Get a specific cookie name
     *
     * @param string $name the cookie name to retrieve
     * @param mixed|null $defaultValue the default value to return
     * @return mixed the cookie value if available otherwise the @var $defaultValue
     */
    public function getCookie($name, $defaultValue = null)
    {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return $defaultValue;
    }

    public function setProtocol($https = false)
    {
        if ($https) {
            $this->_protocol = 'https';
        }
        else {
            $this->_protocol = 'http';
        }
        return $this;
    }

    public function getProtocol()
    {
        return $this->_protocol;
    }

    public function setHostName($hostName)
    {
        $this->_hostName = $hostName;
        return $this;
    }

    public function getHostName()
    {
        return $this->_hostName;
    }

    public function setHeaders($headers)
    {
        $this->_headers = $headers;
        return $this;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function getHeader($name)
    {
        return $this->_headers[$name];
    }

    public function setHeader($name, $value)
    {
        $this->_headers[$name] = $value;
        return $this;
    }

    /**
     * Returns the request as a string.
     *
     * @return string The request
     */
    public function __toString()
    {
        $queryString = $this->getQueryString();

        $headersString = '';
        foreach ($this->getHeaders() as $name => $value) {
            $headersString .= "$name: $value\r\n";
        }

        return sprintf(
            "%s %s%s %s\r\n%s\r\n%s",
            $this->getMethod(),
            $this->getUri(),
            $queryString ? "?$queryString" : '',
            $this->getHttpProtocol(),
            $headersString,
            $this->getRawBody()
        );
    }
}
