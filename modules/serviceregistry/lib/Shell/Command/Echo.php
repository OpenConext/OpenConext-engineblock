<?php

class Shell_Command_Echo extends Shell_Command_Abstract
{
    const COMMAND = 'echo';

    protected $_output;

    public function __construct($output)
    {
        $this->_output = $output;
    }

    public function _buildCommand()
    {
        return self::COMMAND . ' ' . escapeshellarg($this->_output);
    }
}