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
        $this->feedbackInfo['AuthnFailedResponse'] = $response;
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
