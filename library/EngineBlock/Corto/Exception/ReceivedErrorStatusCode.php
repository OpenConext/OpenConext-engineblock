<?php

class EngineBlock_Corto_Exception_ReceivedErrorStatusCode extends EngineBlock_Exception
{
    /**
     * @var array
     */
    private $feedbackInfo;

    public function setFeedbackStatusCode($statusCode)
    {
        $this->feedbackInfo['statusCode'] = $statusCode;
    }

    public function setFeedbackStatusMessage($statusMessage)
    {
        $this->feedbackInfo['statusMessage'] = $statusMessage;
    }

    public function getFeedbackInfo()
    {
        return $this->feedbackInfo;
    }
}