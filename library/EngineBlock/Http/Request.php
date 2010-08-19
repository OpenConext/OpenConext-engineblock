<?php
 
class EngineBlock_Http_Request 
{
    protected $_method;
    protected $_protocol;
    protected $_hostName;
    protected $_uri;
    protected $_queryParameters = array();
    protected $_queryString;

    public static function createFromEnvironment()
    {
        $request = new self();

        $request->setProtocol((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on'));
        $request->setMethod($_SERVER['REQUEST_METHOD']);
        $request->setHostName($_SERVER['HTTP_HOST']);
        $request->setUri($_SERVER['REQUEST_URI']);
        $request->setQueryString($_SERVER['QUERY_STRING']);        
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
        $this->_uri = $uri;
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
    }

    protected function _setQueryParameters($queryString)
    {
        $this->_queryParameters = array();
        $queryParts = explode("&", $queryString);
        foreach($queryParts as $queryPart) {
            if (preg_match("/^(.+)=(.*)$/", $queryPart, $keyAndValue))  {
                $key    = $keyAndValue[1];
                $value  = $keyAndValue[2];

                $this->_queryParameters[$key] = $value;
            }
        }
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
}