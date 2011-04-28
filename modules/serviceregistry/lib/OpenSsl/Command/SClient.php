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

    public function setConnectTo($host="localhost", $port=443)
    {
        $this->_connectTo = array(
            'host' => $host,
            'port' => $port,
        );
        return $this;
    }

    public function _buildCommand()
    {
        $command = self::COMMAND;
        if (isset($this->_connectTo)) {
            $command .= " -connect {$this->_connectTo['host']}:{$this->_connectTo['port']}";
        }
        return $command;
    }
}
