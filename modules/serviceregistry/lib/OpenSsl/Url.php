<?php
/**
 *
 */

/**
 *
 */ 
class OpenSsl_Url
{
    const HTTP_PORT = 80;
    const HTTP_CONNECTION_TIMEOUT = 30;
    const HTTP_SUCCESS_CODE = 200;

    protected $_url;
    protected $_parsed;

    /**
     * @var OpenSsl_Command_SClient
     */
    protected $_connection;

    public function __construct($url)
    {
        $this->_url     = $url;
        $this->_parsed  = parse_url($url);
        if (!$this->_parsed) {
            throw new Exception("Url '$url' is not a valid URL");
        }
    }

    public function getHost()
    {
        return $this->_parsed['host'];
    }

    public function isHttps()
    {
        return ($this->_parsed && strtolower($this->_parsed['scheme'])==='https');
    }

    public function connect()
    {
        $sslClientcommand = new OpenSsl_Command_SClient();
        $sslClientcommand->setConnectTo($this->_parsed['host']);
        $sslClientcommand->execute();
        $this->_connection = $sslClientcommand;

        return ($sslClientcommand->getExitStatus() === 0);
    }

    public function getCertificate()
    {
        if (!$this->_connection) {
            $this->connect();
        }

        $x509Command = new OpenSsl_Command_X509();
        $x509Command->execute($this->_connection->getOutput());
        $pem = $x509Command->getOutput();

        return new OpenSsl_Certificate($pem);
    }
}
