<?php

class EngineBlock_Log_Message_AdditionalInfo
{
    protected $_severity;
    protected $_location;
    protected $_userId;
    protected $_idp;
    protected $_sp;
    protected $_details = "";
    protected $_messagePrefix;

    public static function createFromException(EngineBlock_Exception $e)
    {
        /** @var EngineBlock_Log_Message_AdditionalInfo $info */
        $info = new static();
        $info->_userId  = $e->userId;
        $info->_idp     = $e->idpEntityId;
        $info->_sp      = $e->spEntityId;

        if (!empty($e->description)) {
            $info->_details = $e->description . PHP_EOL;
        }

        $traces = array(get_class($e) . ': ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
        $prev = $e;
        while ($prev = $prev->getPrevious()) {
            $traces[] = get_class($prev) . ': ' . $prev->getMessage() . PHP_EOL . $prev->getTraceAsString();
        }
        $info->_details .= implode(PHP_EOL . PHP_EOL, $traces);

        $info->_location= $e->getFile() . ':' . $e->getLine();
        switch ($e->getSeverity()) {
            case EngineBlock_Exception::CODE_EMERGENCY: $info->_severity = 'EMERG'; break;
            case EngineBlock_Exception::CODE_ALERT:     $info->_severity = 'ALERT'; break;
            case EngineBlock_Exception::CODE_CRITICAL:  $info->_severity = 'CRITICAL'; break;
            case EngineBlock_Exception::CODE_ERROR:     $info->_severity = 'ERROR'; break;
            case EngineBlock_Exception::CODE_WARNING:   $info->_severity = 'WARNING'; break;
            case EngineBlock_Exception::CODE_NOTICE:    $info->_severity = 'NOTICE'; break;
            default: $info->_severity = 'ERROR';
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
        $this->_severity = $severity;
    }

    public function getSeverity()
    {
        return $this->_severity;
    }

    public function setLocation($line)
    {
        $this->_location = $line;
    }

    public function getLocation()
    {
        return $this->_location;
    }

    public function setDetails($details)
    {
        $this->_details = $details;
        return $this;
    }

    public function getDetails()
    {
        return $this->_details;
    }

    public function setIdp($idp)
    {
        $this->_idp = $idp;
        return $this;
    }

    public function getIdp()
    {
        return $this->_idp;
    }

    public function setSp($sp)
    {
        $this->_sp = $sp;
        return $this;
    }

    public function getSp()
    {
        return $this->_sp;
    }

    public function setUserId($userId)
    {
        $this->_userId = $userId;
        return $this;
    }

    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * @param string $prefix
     * @return \EngineBlock_Log_Message_AdditionalInfo
     */
    public function setMessagePrefix($prefix)
    {
        $this->_messagePrefix = (string)$prefix;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessagePrefix()
    {
        return $this->_messagePrefix;
    }


    public function toArray()
    {
        $array = array();
        $array['severity']       = $this->_severity;
        $array['location']       = $this->_location;
        $array['userId']         = $this->_userId;
        $array['idp']            = $this->_idp;
        $array['sp']             = $this->_sp;
        $array['details']        = $this->_details;
        $array['message_prefix'] = $this->_messagePrefix;
        return $array;
    }
}
