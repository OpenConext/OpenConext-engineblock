<?php

/**
 * Trigger an error on responses that do not contain the success code.
 */
class EngineBlock_Corto_Filter_Command_ValidateSuccessfulResponse extends EngineBlock_Corto_Filter_Command_Abstract
{
    public function execute()
    {
        $status = $this->_response->getStatus();
        if ($status['Code'] === \SAML2_Const::STATUS_SUCCESS) {
            return;
        }

        $statusCodeDescription = $status['Code'];
        if (isset($status['SubCode'])) {
            $statusCodeDescription .= '/' . $status['SubCode'];
        }
        $statusCodeDescription = str_replace('urn:oasis:names:tc:SAML:2.0:status:', '', $statusCodeDescription);

        $statusMessage = !empty($status['Message']) ? $status['Message'] : '(No message provided)';

        $exception = new EngineBlock_Corto_Exception_ReceivedErrorStatusCode(
            'Response received with Status: ' .
                $statusCodeDescription .
                ' - ' .
                $statusMessage
        );
        $exception->setFeedbackStatusCode($statusCodeDescription);
        $exception->setFeedbackStatusMessage($statusMessage);

        throw $exception;
    }
}
