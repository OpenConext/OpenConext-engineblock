<?php

namespace OpenConext\EngineBlock\Logger\Message;

use EngineBlock_Exception;

final class AdditionalInfo
{
    protected $severity;
    protected $location;
    protected $userId;
    protected $idp;
    protected $sp;
    protected $details = "";
    protected $messagePrefix;

    public static function createFromException(EngineBlock_Exception $e)
    {
        /** @var AdditionalInfo $info */
        $info         = new static();
        $info->userId = $e->userId;
        $info->idp    = $e->idpEntityId;
        $info->sp     = $e->spEntityId;

        if (!empty($e->description)) {
            $info->details = $e->description . PHP_EOL;
        }

        $traces = array(get_class($e) . ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        $prev = $e;
        while ($prev = $prev->getPrevious()) {
            $traces[] = get_class($prev) . ': ' . $prev->getMessage() . PHP_EOL . $prev->getTraceAsString();
        }
        $info->details .= implode(PHP_EOL . PHP_EOL, $traces);

        $info->location = $e->getFile() . ':' . $e->getLine();
        switch ($e->getSeverity()) {
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

    public static function create()
    {
        return new static();
    }

    protected function __construct()
    {
    }

    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }

    public function getSeverity()
    {
        return $this->severity;
    }

    public function setLocation($line)
    {
        $this->location = $line;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setIdp($idp)
    {
        $this->idp = $idp;

        return $this;
    }

    public function getIdp()
    {
        return $this->idp;
    }

    public function setSp($sp)
    {
        $this->sp = $sp;

        return $this;
    }

    public function getSp()
    {
        return $this->sp;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

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
