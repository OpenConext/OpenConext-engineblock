<?php

class Authentication_Controller_IdentityProvider extends EngineBlock_Controller_Abstract
{
    public function singleSignOnAction()
    {
        $this->setNoRender();

        $this->_singleSignOn(
            'singleSignOn', func_get_args()
        );
    }

    public function unsolicitedSingleSignOnAction()
    {
        $this->setNoRender();

        $this->_singleSignOn(
            'unsolicitedSingleSignOn', func_get_args()
        );
    }

    /**
     * Method handling singleSignOn and unsolicitedSingleSignOn
     *
     * @param string $service
     * @param array $arguments
     * @throws EngineBlock_Exception
     */
    protected function _singleSignOn($service = 'singleSignOn', array $arguments = array())
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();

        try {
            $proxyServer = new EngineBlock_Corto_Adapter();

            $idPEntityId = NULL;

            // Optionally allow /single-sign-on/vo:myVoId/remoteIdPHash or
            // /single-sign-on/remoteIdPHash/vo:myVoId/key:20140420
            foreach ($arguments as $argument) {
                if (substr($argument, 0, 3) == 'vo:') {
                    $proxyServer->setVirtualOrganisationContext(substr($argument, 3));
                } else if (substr($argument, 0, 4) === 'key:') {
                    $proxyServer->setKeyId(substr($argument, 4));
                }
                else {
                    $idPEntityId = $argument;
                }
            }

            // should be 'singleSignOn' or 'unsolicitedSingleSignOn'
            if (!is_callable(array($proxyServer, $service))) {
                throw new EngineBlock_Exception(
                    'Invalid service name in IdentityProvider controller',
                    EngineBlock_Exception::CODE_ERROR
                );
            }

            // call service
            $proxyServer->$service($idPEntityId);
        }
        catch (EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException $e) {
            $application->getLogInstance()->notice(
                "Unable to receive message",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unable-to-receive-message');
        }
        catch (EngineBlock_Corto_Exception_UserNotMember $e) {
            $application->getLogInstance()->notice(
                "User is not a member",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/vomembershiprequired');
        }
        catch (EngineBlock_Corto_Module_Services_SessionLostException $e) {
            $application->getLogInstance()->notice(
                "Session lost",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/session-lost');
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
        catch (EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException $e) {
            $application->getLogInstance()->notice(
                "No Identity Providers",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/no-idps'
            );
        }
        catch (EngineBlock_Corto_Exception_InvalidAcsLocation $e) {
            $application->getLogInstance()->notice(
                "Invalid ACS location",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/invalidAcsLocation'
            );
        }
    }

    public function processWayfAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processWayf();
    }

    public function metadataAction()
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        $proxyServer = new EngineBlock_Corto_Adapter();

        foreach (func_get_args() as $argument) {
            if (substr($argument, 0, 3) === 'vo:') {
                $proxyServer->setVirtualOrganisationContext(substr($argument, 3));
            } else if (substr($argument, 0, 4) === 'key:') {
                $proxyServer->setKeyId(substr($argument, 4));
            } else {
                $application->getLogInstance()->notice("Ignoring unknown argument '$argument'.");
            }
        }

        try {
            $proxyServer->idPMetadata();
        }
        catch (EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unknown-service-provider?entity-id=' . urlencode($e->getEntityId()));
        }

    }

    public function processConsentAction()
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        try {
            $proxyServer = new EngineBlock_Corto_Adapter();
            $proxyServer->processConsent();
        }
        catch (EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unable-to-receive-message');
        }
        catch (EngineBlock_Corto_Exception_UserNotMember $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/vomembershiprequired');
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
        catch (EngineBlock_Attributes_Manipulator_CustomException $e) {
            $_SESSION['feedback_custom'] = $e->getFeedback();
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/custom');
        }
        catch (EngineBlock_Corto_Exception_NoConsentProvided $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/no-consent');
        }
    }

    public function helpConsentAction($argument = null)
    {

    }

    public function helpDiscoverAction($argument = null)
    {

    }

    public function requestAccessAction()
    {
        $this->queryParameters = $this->_getRequest()->getQueryParameters();
    }

    public function performRequestAccessAction()
    {
        if (!isset($_POST['institution']) && $this->_requiredDataValid(array("name", "email", "comment"))) {
            $this->_sendRequestAccessMail(
                urldecode($_POST['idpEntityId']),
                urldecode($_POST['spEntityId']),
                $_POST['name'],
                $_POST['email'],
                $_POST['comment'] );
        } elseif ($this->_requiredDataValid(array("name", "email", "institution", "comment"))) {
            $this->_sendManualInstitutionRequestAccessMail(
                urldecode($_POST['spEntityId']),
                $_POST['name'],
                $_POST['email'],
                $_POST['institution'],
                $_POST['comment'] );
        } else {
            $this->queryParameters = $_POST;
            $this->renderAction("RequestAccess");
        }
    }

    public function certificateAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();

        foreach (func_get_args() as $argument) {
            if (substr($argument, 0, 4) === 'key:') {
                $proxyServer->setKeyId(substr($argument, 4));
            } else {
                EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()->notice(
                    "Ignoring unknown argument '$argument'."
                );
            }
        }

        $proxyServer->idpCertificate();
    }

    protected function _requiredDataValid($names)
    {
        $dataValid = true;
        foreach ($names as $name) {
            if (empty($_POST[$name]) || ($name === 'email' && !filter_var($_POST[$name], FILTER_VALIDATE_EMAIL))) {
                $name = $name.'Error';
                $this->$name = true;
                $dataValid = false;
            }
        }
        return $dataValid;
    }

    protected function _sendRequestAccessMail($idp, $sp, $name, $email, $comment) {
        $body = <<<EOT
There has been a request to allow access for IdP '$idp' to SP '$sp'. The request was made by:

$name <$email>

The comment was:

$comment

EOT;
        $mailer = new Zend_Mail('UTF-8');
        $mailer->setFrom($_POST['email']);
        $mailer->addTo(EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue('email')->help);
        $mailer->setSubject(sprintf("Request for IdP access (%s)", gethostname()));
        $mailer->setBodyText($body);
        $mailer->send();
    }

    protected function _sendManualInstitutionRequestAccessMail($sp, $name, $email, $institution, $comment) {
        $body = <<<EOT
There has been a request to allow access for institution '$institution' to SP '$sp'. The request was made by:

$name <$email>

The comment was:

$comment

EOT;
        $mailer = new Zend_Mail('UTF-8');
        $mailer->setFrom($_POST['email']);
        $mailer->addTo(EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValue('email')->help);
        $mailer->setSubject(sprintf("Request for institution access (%s)", gethostname()));
        $mailer->setBodyText($body);
        $mailer->send();
    }
}
