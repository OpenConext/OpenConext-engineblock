<?php
 
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
        foreach ($this->_headers as $headerName => $headerValue) {
            header("$headerName: $headerValue");
        }

        echo $this->_body;
    }
}
