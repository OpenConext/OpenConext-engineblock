<?php

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