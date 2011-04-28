<?php
/**
 *
 */

abstract class Shell_Command_Abstract implements Shell_Command_Interface
{
    protected $_exitStatus;

    protected $_output;

    protected $_errors;

    abstract protected function _buildCommand();

    public function execute($stdIn = "")
    {
        $command = $this->_buildCommand();

        $descSpec = array(
            0 => array('pipe', 'r'), // stdin
            1 => array('pipe', 'w'), // stdout
            2 => array('pipe', 'a'), // stderr
        );

        $pipes = array();
        $process = proc_open($command, $descSpec, $pipes);

        if (!is_resource($process)) {
            throw new Exception('Failed to execute command: ' . $command);
        }

        if (fwrite($pipes[0], $stdIn) === FALSE) {
            throw new Exception('Failed to write certificate for pipe.');
        }
        fclose($pipes[0]);

        $output = '';
        $errors = '';
        while (!feof($pipes[1]) && !feof($pipes[2])) {
            $output .= fgets($pipes[1]);
            $errors .= fgets($pipes[2]);
        }
        fclose($pipes[1]);

        $this->_errors = $errors;
        $this->_output = $output;
        $this->_exitStatus = proc_close($process);
        return $this;
    }

    public function getExitStatus()
    {
        return $this->_exitStatus;
    }

    public function getOutput()
    {
        return $this->_output;
    }

    public function getErrors()
    {
        return $this->_errors;
    }
}