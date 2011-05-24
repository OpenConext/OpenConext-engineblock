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

/**
 *
 */ 
class ServiceRegistry_Cron_Logger
{
    protected $_hasWarnings = false;
    protected $_hasErrors = false;
    protected $_summary = array();

    public function __construct()
    {
    }

    public function notice($message, $entityId = null)
    {
        $arguments = func_get_args();
        $message = array_shift($arguments);
        array_unshift($arguments, 'Notice');
        array_unshift($arguments, $message);
        call_user_func_array(array($this, 'log'), $arguments);
    }

    public function warn($message, $entityId = null)
    {
        $this->_hasWarnings = true;
        $arguments = func_get_args();
        $message = array_shift($arguments);
        array_unshift($arguments, 'Warning');
        array_unshift($arguments, $message);
        call_user_func_array(array($this, 'log'), $arguments);
    }

    public function error($message, $entityId = null)
    {
        $this->_hasErrors = true;
        $arguments = func_get_args();
        $message = array_shift($arguments);
        array_unshift($arguments, 'Error');
        array_unshift($arguments, $message);
        call_user_func_array(array($this, 'log'), $arguments);
    }

    public function log($message, $namespace1 = null, $namespace2 = null)
    {
        $arguments = func_get_args();
        $message = array_shift($arguments);

        $prefix = "";
        foreach ($arguments as $argument) {
            if (!is_null($argument)) {
                $prefix .= '[' . $argument . ']';
            }
        }
        $this->_summary[] = $prefix . $message;
    }

    public function hasWarnings()
    {
        return $this->_hasWarnings;
    }

    public function hasErrors()
    {
        return $this->_hasErrors;
    }

    public function getSummaryLines()
    {
        return $this->_summary;
    }
}
