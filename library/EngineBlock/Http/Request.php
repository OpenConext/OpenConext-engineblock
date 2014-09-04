<?php

class EngineBlock_Http_Request 
{
    protected $_method;

    protected $_protocol;
    protected $_hostName;
    protected $_uri;

    protected $_queryParameters = array();
    protected $_queryString;

    protected $_postParameters;

    protected $_headers;

    public static function createFromEnvironment()
    {
        $request = new self();

        $request->setProtocol((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on'));
        $request->setMethod($_SERVER['REQUEST_METHOD']);
        $request->setHostName($_SERVER['HTTP_HOST']);

        $queryStart = strpos($_SERVER['REQUEST_URI'], '?');
        if ($queryStart !== false) {
            $request->setUri(substr($_SERVER['REQUEST_URI'], 0, $queryStart));
        }
        else {
            $request->setUri($_SERVER['REQUEST_URI']);
        }
        $request->setQueryString($_SERVER['QUERY_STRING']);
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
     * @return all POST parameters as an array for the given request
     */
    public function getPostParameters()
    {
        return $this->_postParameters;
    }

    /**
     * Get the value of a specific POST variable based on a given parameter name
     *
     * @param $param the name of the post parameter
     * @return the value of the requested post parameter || null when it doesn't exist
     */
    public function getPostParameter($param)
    {
        if (array_key_exists($param, $this->_postParameters)) {
            return $this->_postParameters[$param];
        }
        return null;
    }

    /**
     * Get a specific cookie name
     *
     * @param $name the cookie name to retrieve
     * @param null $defaultValue the default value to return
     * @return the cookie value if available otherwise the @var $defaultValue
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
}
