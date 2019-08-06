<?php

class EngineBlock_Corto_Exception_ReceivedErrorStatusCode extends EngineBlock_Exception implements EngineBlock_Corto_Exception_HasFeedbackInfoInterface
{
    /**
     * @var array
     */
    private $feedbackInfo;

    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    private $response;

    public function setFeedbackStatusCode($statusCode)
    {
        $this->feedbackInfo['statusCode'] = $statusCode;
    }

    public function setFeedbackStatusMessage($statusMessage)
    {
        $this->feedbackInfo['statusMessage'] = $statusMessage;
    }

    public function setResponse(EngineBlock_Saml2_ResponseAnnotationDecorator $response)
    {
        $this->response = $response;
    }

    public function getFeedbackInfo()
    {
        return $this->feedbackInfo;
    }

    public function getFeedbackStatusCode()
    {
        return $this->feedbackInfo['statusCode'];
    }

    public function getFeedbackStatusMessage()
    {
        return $this->feedbackInfo['statusMessage'];
    }

    /**
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    public function getResponse()
    {
        return $this->response;
    }
}
