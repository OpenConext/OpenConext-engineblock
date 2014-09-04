<?php

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

    public function setBody($body)
    {
        $this->_body = $body;
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
