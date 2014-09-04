<?php

/**
 * Trigger an error on responses that do not contain the success code.
 */
class EngineBlock_Corto_Filter_Command_ValidateSuccessfulResponse extends EngineBlock_Corto_Filter_Command_Abstract
{
    const SAML2_STATUS_CODE_SUCCESS = 'urn:oasis:names:tc:SAML:2.0:status:Success';

    public function execute()
    {
        $status = $this->_response->getStatus();
        $statusCode = $status['Code'];
        if ($statusCode !== self::SAML2_STATUS_CODE_SUCCESS) {
            // Idp returned an error
            $statusMessage = !empty($status['Message']) ? $status['Message'] : '(No message provided)';

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
