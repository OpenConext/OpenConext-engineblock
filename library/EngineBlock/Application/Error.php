<?php

final class EngineBlock_Application_Error
{
    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $file;

    /**
     * @var int
     */
    private $line;

    /**
     * @return EngineBlock_Application_Error|null
     */
    public static function fromLast()
    {
        $errorArray = error_get_last();

        if (!$errorArray) {
            return null;
        }

        return new self($errorArray['type'], $errorArray['message'], $errorArray['file'], $errorArray['line']);
    }

    public function __construct($type, $message, $file, $line)
    {
        $this->type = $type;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @param EngineBlock_Application_Error $other
     * @return bool
     */
    public function equals(self $other)
    {
        return $this == $other;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'type'   => $this->type,
            'message'=> $this->message,
            'file'   => $this->file,
            'line'   => $this->line,
        );
    }
}
