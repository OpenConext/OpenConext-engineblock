<?php

class EngineBlock_Attributes_Manipulator_CustomException extends EngineBlock_Exception
{
    private $_feedback = array();

    public function create($message, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        return new self($message, $severity, $previous);
    }

    public function __construct($message, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }

    public function getFeedback()
    {
        return $this->_feedback;
    }

    public function setFeedbackTitle(array $titles)
    {
        $this->_feedback['title'] = $titles;
        return $this;
    }

    public function setFeedbackDescription(array $descriptions)
    {
        $this->_feedback['description'] = $descriptions;
        return $this;
    }
}