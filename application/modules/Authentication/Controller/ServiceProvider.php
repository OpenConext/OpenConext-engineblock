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
            $application->getLogInstance()->notice(
                "VO membership required",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/vomembershiprequired'
            );
        }
        catch (EngineBlock_Corto_Module_Services_SessionLostException $e) {
            $application->getLogInstance()->notice(
                "Session Lost",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/session-lost'
            );
        }
        catch (EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException $e) {
            $application->getLogInstance()->notice(
                "Unable to receive message",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/unable-to-receive-message'
            );
        }
        catch (EngineBlock_Corto_Exception_UnknownIssuer $e) {
            $application->getLogInstance()->notice(
                "Unknown Issuer",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unknown-issuer?entity-id=' . urlencode($e->getEntityId()) .
                    '&destination=' . urlencode($e->getDestination())
            );
        }
        catch (EngineBlock_Corto_Exception_MissingRequiredFields $e) {
            $application->getLogInstance()->notice(
                "Missing Required Fields",
                array('exception' => $e)
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
            $application->getLogInstance()->notice(
                "Unsupported Binding",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/invalid-acs-binding');
        }
        catch (EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e) {
            // Add extra feedback info
            $application->getLogInstance()->notice(
                "Received Error Status Code",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/received-error-status-code',
                $e->getFeedbackInfo()
            );
        }
        catch (EngineBlock_Corto_Module_Bindings_SignatureVerificationException $e) {
            $application->getLogInstance()->warning(
                "Unable to verify signature, cert wrong?",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/received-invalid-signed-response'
            );
        }
        catch (EngineBlock_Corto_Module_Bindings_VerificationException $e) {
            $application->getLogInstance()->notice(
                "Unable to verify message",
                array('exception' => $e)
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
