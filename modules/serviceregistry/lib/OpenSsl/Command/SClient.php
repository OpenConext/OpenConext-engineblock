<?php
/**
 *
 */

/**
 *
 */ 
class OpenSsl_Command_SClient extends Shell_Command_Abstract
{
    const COMMAND = 'openssl s_client';

    protected $_connectTo;
    protected $_showCerts;
    protected $_certificateAuthorityFile;

    public function setConnectTo($host="localhost", $port=443)
    {
        $this->_connectTo = array(
            'host' => $host,
            'port' => $port,
        );
        return $this;
    }

    public function setShowCerts($showCerts)
    {
        $this->_showCerts = $showCerts;
    }

    public function setCertificateAuthorityFile($file)
    {
        $this->_certificateAuthorityFile = $file;
        return $this;
    }

    public function _buildCommand()
    {
        $command = self::COMMAND;
        if (isset($this->_connectTo)) {
            $command .= " -connect {$this->_connectTo['host']}:{$this->_connectTo['port']}";
        }
        if (isset($this->_showCerts) && $this->_showCerts) {
            $command .= ' -showcerts';
        }
        if (isset($this->_certificateAuthorityFile)) {
            $command .= ' -CAfile ' . escapeshellarg($this->_certificateAuthorityFile);
        }
        return $command;
    }
}
