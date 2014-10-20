<?php

class Authentication_Controller_ServiceProvider extends EngineBlock_Controller_Abstract
{
    public function consumeAssertionAction()
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        $proxyServer = new EngineBlock_Corto_Adapter();

        try {
            $proxyServer->consumeAssertion();
        }
        catch (EngineBlock_Corto_Exception_UserNotMember $e) {
            $application->getLogInstance()->log(
                "VO membership required",
                EngineBlock_Log::NOTICE,
                EngineBlock_Log_Message_AdditionalInfo::createFromException($e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/vomembershiprequired'
            );
        }
        catch (EngineBlock_Corto_Module_Services_SessionLostException $e) {
            $application->getLogInstance()->log(
                "Session Lost",
                EngineBlock_Log::NOTICE,
                EngineBlock_Log_Message_AdditionalInfo::createFromException($e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/session-lost'
            );
        }
        catch (EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException $e) {
            $application->getLogInstance()->log(
                "Unable to receive message",
                EngineBlock_Log::NOTICE,
                EngineBlock_Log_Message_AdditionalInfo::createFromException($e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/unable-to-receive-message'
            );
        }
        catch (EngineBlock_Corto_Exception_UnknownIssuer $e) {
            $application->getLogInstance()->log(
                "Unknown Issuer",
                EngineBlock_Log::NOTICE,
                EngineBlock_Log_Message_AdditionalInfo::createFromException($e)
            );
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unknown-issuer?entity-id=' . urlencode($e->getEntityId()) .
                    '&destination=' . urlencode($e->getDestination())
            );
        }
        catch (EngineBlock_Corto_Exception_MissingRequiredFields $e) {
            $application->getLogInstance()->log(
                "Missing Required Fields",
                EngineBlock_Log::NOTICE,
                EngineBlock_Log_Message_AdditionalInfo::createFromException($e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/missing-required-fields'
            );
        }
        catch (EngineBlock_Attributes_Manipulator_CustomException $e) {
            $_SESSION['feedback_custom'] = $e->getFeedback();
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/custom'
            );
        }
        catch (EngineBlock_Corto_Module_Bindings_UnsupportedBindingException $e) {
            $application->getLogInstance()->log(
                "Unsupported Binding",
                EngineBlock_Log::NOTICE,
                EngineBlock_Log_Message_AdditionalInfo::createFromException($e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/invalid-acs-binding');
        }
        catch (EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e) {
            // Add extra feedback info
            $application->getLogInstance()->log(
                "Received Error Status Code",
                EngineBlock_Log::NOTICE,
                EngineBlock_Log_Message_AdditionalInfo::createFromException($e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/received-error-status-code',
                $e->getFeedbackInfo()
            );
        }
        catch (EngineBlock_Corto_Module_Bindings_SignatureVerificationException $e) {
            $application->getLogInstance()->log(
                "Unable to verify signature, cert wrong?",
                EngineBlock_Log::WARN,
                EngineBlock_Log_Message_AdditionalInfo::createFromException($e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/received-invalid-signed-response'
            );
        }
        catch (EngineBlock_Corto_Module_Bindings_VerificationException $e) {
            $application->getLogInstance()->log(
                "Unable to verify message",
                EngineBlock_Log::NOTICE,
                EngineBlock_Log_Message_AdditionalInfo::createFromException($e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/received-invalid-response'
            );
        }
    }

    public function processConsentAction()
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        $proxyServer = new EngineBlock_Corto_Adapter();

        try {
            $proxyServer->processConsent();
        }
        catch (EngineBlock_Corto_Module_Services_SessionLostException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/session-lost');
        }
        catch (EngineBlock_Corto_Exception_UserNotMember $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/vomembershiprequired');
        }
        catch (EngineBlock_Attributes_Manipulator_CustomException $e) {
            $_SESSION['feedback_custom'] = $e->getFeedback();
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/custom');
        }
    }

    /**
     * The metadata for EngineBlock as a Service Provider
     *
     * @return void
     */
    public function metadataAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();

        foreach (func_get_args() as $argument) {
            if (substr($argument, 0, 3) === 'vo:') {
                $proxyServer->setVirtualOrganisationContext(substr($argument, 3));
            } else if (substr($argument, 0, 4) === 'key:') {
                $proxyServer->setKeyId(substr($argument, 4));
            } else {
                EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->notice(
                    "Ignoring unknown argument '$argument'."
                );
            }
        }


        $proxyServer->sPMetadata();
    }

    public function certificateAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();

        foreach (func_get_args() as $argument) {
            if (substr($argument, 0, 3) === 'vo:') {
                $proxyServer->setVirtualOrganisationContext(substr($argument, 3));
            } else if (substr($argument, 0, 4) === 'key:') {
                $proxyServer->setKeyId(substr($argument, 4));
            } else {
                EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->notice(
                    "Ignoring unknown argument '$argument'."
                );
            }
        }

        $proxyServer->idpCertificate();
    }

    public function debugAction()
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        try {
            $proxyServer = new EngineBlock_Corto_Adapter();
            $proxyServer->debugSingleSignOn();
        }
        catch (EngineBlock_Corto_Module_Services_SessionLostException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/session-lost');
        }
        catch (EngineBlock_Corto_Exception_UnknownIssuer $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unknown-issuer?entity-id=' . urlencode($e->getEntityId()) .
                    '&destination=' . urlencode($e->getDestination())
            );
        }
    }
}
