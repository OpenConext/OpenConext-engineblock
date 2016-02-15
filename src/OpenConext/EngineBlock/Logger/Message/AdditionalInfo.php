<?php

namespace OpenConext\EngineBlock\Logger\Message;

use EngineBlock_Exception;

final class AdditionalInfo
{
    /**
     * @var string
     */
    protected $severity;

    /**
     * @var string
     */
    protected $location;

    /**
     * @var string|null
     */
    protected $userId;

    /**
     * @var string|null
     */
    protected $idp;

    /**
     * @var string|null
     */
    protected $sp;

    /**
     * @var string
     */
    protected $details = "";

    /**
     * @var string
     */
    protected $messagePrefix;

    public static function createFromException(EngineBlock_Exception $exception)
    {
        $info         = new self();
        $info->userId = $exception->userId;
        $info->idp    = $exception->idpEntityId;
        $info->sp     = $exception->spEntityId;

        if (!empty($exception->description)) {
            $info->details = $exception->description . PHP_EOL;
        }

        $traces = array(
            get_class($exception) . ': ' . $exception->getMessage() . PHP_EOL . $exception->getTraceAsString()
        );

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
        return array(
            'severity'       => $this->severity,
            'location'       => $this->location,
            'userId'         => $this->userId,
            'idp'            => $this->idp,
            'sp'             => $this->sp,
            'details'        => $this->details,
            'message_prefix' => $this->messagePrefix
        );
    }
}
