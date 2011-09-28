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
    protected $_notices  = array();
    protected $_warnings = array();
    protected $_errors   = array();
    protected $_namespaces = array();

    public function __construct()
    {
    }

    public function with($namespace)
    {
        $this->_namespaces[] = $namespace;
        return $this;
    }

    public function notice($message)
    {
        $this->_notices[$message] = array(
            'message' => $message,
            'namespaces' => $this->_namespaces,
        );
        $this->_namespaces = array();
        return $this;
    }

    public function warn($message)
    {
        $this->_warnings[] = array(
            'message' => $message,
            'namespaces' => $this->_namespaces,
        );
        $this->_namespaces = array();
        return $this;
    }

    public function error($message, $entityId = null)
    {
        $this->_errors[] = array(
            'message' => $message,
            'namespaces' => $this->_namespaces,
        );
        $this->_namespaces = array();
        return $this;
    }

    public function hasWarnings()
    {
        return !empty($this->_warnings);
    }

    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    public function getNotices()
    {
        return $this->_notices;
    }

    public function getNamespacedNotices()
    {
        return $this->_namespaceMessages($this->_notices);
    }

    public function getWarnings()
    {
        return $this->_warnings;
    }

    public function getNamespacedWarnings()
    {
        return $this->_namespaceMessages($this->_warnings);
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getNamespacedErrors()
    {
        return $this->_namespaceMessages($this->_errors);
    }

    protected function _namespaceMessages($messages)
    {
        $namespacedMessages = array();
        foreach ($messages as $message) {
            $pointer = &$namespacedMessages;
            if (empty($message['namespaces'])) {
                $pointer[] = $message['message'];
                continue;
            }

            foreach ($message['namespaces'] as $namespace) {
                if (!isset($pointer[$namespace])) {
                    $pointer[$namespace] = array();
                }
                $pointer = &$pointer[$namespace];
            }
            $pointer[] = $message['message'];
        }
        return $namespacedMessages;
    }

    public function getSummaryLines()
    {
        $summaryLines = array();
        $messagesCollection = array(
            "Error"  => $this->_errors,
            "Warning"=> $this->_warnings,
            "Notice" => $this->_notices,
        );
        foreach ($messagesCollection as $label => $messages) {
            foreach ($messages as $message) {
                $summaryLine = "&lt;$label&gt;";
                foreach ($message['namespaces'] as $namespace) {
                    $summaryLine .= "&lt;$namespace&gt;";
                }
                $summaryLine .= ' ' . $message['message'];
                $summaryLines[] = $summaryLine;
            }
        }
        return $summaryLines;
    }
}
