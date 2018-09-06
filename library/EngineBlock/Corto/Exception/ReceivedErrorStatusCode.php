<?php

class EngineBlock_Corto_Exception_ReceivedErrorStatusCode extends EngineBlock_Exception implements EngineBlock_Corto_Exception_HasFeedbackInfoInterface
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