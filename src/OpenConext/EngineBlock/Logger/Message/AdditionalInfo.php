<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Logger\Message;

use EngineBlock_Exception;

final class AdditionalInfo
{
    /**
     * @var string
     */
    private $severity;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string|null
     */
    private $userId;

    /**
     * @var string|null
     */
    private $idp;

    /**
     * @var string|null
     */
    private $sp;

    /**
     * @var string
     */
    private $details = "";

    /**
     * @var string
     */
    private $messagePrefix;

    public static function createFromException(EngineBlock_Exception $exception)
    {
        $info         = new self();
        $info->userId = $exception->userId;
        $info->idp    = $exception->idpEntityId;
        $info->sp     = $exception->spEntityId;

        if (!empty($exception->description)) {
            $info->details = $exception->description . PHP_EOL;
        }

        $traces = [
            get_class($exception) . ': ' . $exception->getMessage() . PHP_EOL . $exception->getTraceAsString()
        ];

        $previous = $exception;
        while ($previous = $previous->getPrevious()) {
            $traces[] = get_class($previous) . ': ' . $previous->getMessage() . PHP_EOL . $previous->getTraceAsString();
        }

        $info->details .= implode(PHP_EOL . PHP_EOL, $traces);

        $info->location = $exception->getFile() . ':' . $exception->getLine();
        switch ($exception->getSeverity()) {
            case EngineBlock_Exception::CODE_EMERGENCY:
                $info->severity = 'EMERG';
                break;
            case EngineBlock_Exception::CODE_ALERT:
                $info->severity = 'ALERT';
                break;
            case EngineBlock_Exception::CODE_CRITICAL:
                $info->severity = 'CRITICAL';
                break;
            case EngineBlock_Exception::CODE_ERROR:
                $info->severity = 'ERROR';
                break;
            case EngineBlock_Exception::CODE_WARNING:
                $info->severity = 'WARNING';
                break;
            case EngineBlock_Exception::CODE_NOTICE:
                $info->severity = 'NOTICE';
                break;
            default:
                $info->severity = 'ERROR';
        }

        return $info;
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    private function __construct()
    {
    }

    /**
     * @param string $severity
     * @return $this
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @param string $line
     * @return $this
     */
    public function setLocation($line)
    {
        $this->location = $line;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $idp
     * @return $this
     */
    public function setIdp($idp)
    {
        $this->idp = $idp;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIdp()
    {
        return $this->idp;
    }

    /**
     * @param string $sp
     * @return $this
     */
    public function setSp($sp)
    {
        $this->sp = $sp;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSp()
    {
        return $this->sp;
    }

    /**
     * @param string $userId
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $prefix
     * @return AdditionalInfo
     */
    public function setMessagePrefix($prefix)
    {
        $this->messagePrefix = (string)$prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessagePrefix()
    {
        return $this->messagePrefix;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'severity'       => $this->severity,
            'location'       => $this->location,
            'userId'         => $this->userId,
            'idp'            => $this->idp,
            'sp'             => $this->sp,
            'details'        => $this->details,
            'message_prefix' => $this->messagePrefix
        ];
    }
}
