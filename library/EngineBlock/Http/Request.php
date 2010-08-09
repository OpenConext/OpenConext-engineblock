<?php
 
class EngineBlock_Http_Request 
{
    protected $_queryString;
    protected $_uri;
    protected $_protocol;
    protected $_hostName;

    public static function createFromEnvironment()
    {
        $request = new self();
        $request->setProtocol((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on'));
        $request->setMethod($_SERVER['REQUEST_METHOD']);
        $request->setHostName($_SERVER['HTTP_HOST']);
        $request->setUri($_SERVER['REDIRECT_URL']);
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
        return $this;
    }

    public function getQueryString()
    {
        return $this->_queryString;
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