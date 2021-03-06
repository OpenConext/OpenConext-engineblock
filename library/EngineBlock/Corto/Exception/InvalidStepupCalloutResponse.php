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

class EngineBlock_Corto_Exception_InvalidStepupCalloutResponse extends EngineBlock_Exception implements EngineBlock_Corto_Exception_HasFeedbackInfoInterface
{
    /**
     * @var array
     */
    private $feedback;

    public function __construct($message, EngineBlock_Corto_Exception_ReceivedErrorStatusCode $exception, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        $this->feedback = $exception->getFeedbackInfo();
        parent::__construct($message, $severity, $previous);
    }

    /**
     * @return array
     */
    public function getFeedbackInfo()
    {
        return $this->feedback;
    }
}
