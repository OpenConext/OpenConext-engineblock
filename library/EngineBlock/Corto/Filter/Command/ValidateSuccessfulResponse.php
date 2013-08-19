<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 * Trigger an error on responses that do not contain the success code.
 */
class EngineBlock_Corto_Filter_Command_ValidateSuccessfulResponse extends EngineBlock_Corto_Filter_Command_Abstract
{
    const SAML2_STATUS_CODE_SUCCESS = 'urn:oasis:names:tc:SAML:2.0:status:Success';

    public function execute()
    {
        $statusCode = $this->_response['samlp:Status']['samlp:StatusCode']['_Value'];
        if ($statusCode !== self::SAML2_STATUS_CODE_SUCCESS) {
            // Idp returned an error

            $statusMessage = $this->_response['samlp:Status']['samlp:StatusMessage']['__v'];

            $exception = new EngineBlock_Corto_Exception_ReceivedErrorStatusCode(
                'Response received with Status: ' .
                    $statusCode .
                    ' - ' .
                    $statusMessage
            );
            $exception->setFeedbackStatusCode($statusCode);
            $exception->setFeedbackStatusMessage($statusMessage);

            throw $exception;
        }
    }
}