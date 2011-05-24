<?php
/**
 * SURFconext Service Registry
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext Service Registry
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
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
        fclose($pipes[2]);

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