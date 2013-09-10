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
     * Method handling signleSignOn and unsolicitedSingleSignOn
     *
     * @param string $service
     * @param array $arguments
     */
    protected function _singleSignOn($service = 'singleSignOn', array $arguments = array())
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();

        try {
            $proxyServer = new EngineBlock_Corto_Adapter();

            $idPEntityId = NULL;

            // Optionally allow /single-sign-on/vo:myVoId/remoteIdPHash or
            // /single-sign-on/remoteIdPHash/vo:myVoId
            foreach ($arguments as $argument) {
                if (substr($argument, 0, 3) == "vo:") {
                    $proxyServer->setVirtualOrganisationContext(substr($argument, 3));
                } else {
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
        catch (EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/no-idps'
            );
        }
    }

    public function processWayfAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processWayf();
    }

    public function metadataAction($argument = null)
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();

        if (substr($argument, 0, 3) == "vo:") {
            $proxyServer->setVirtualOrganisationContext(substr($argument, 3));
        }

        $proxyServer->idPMetadata();
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
    }

    public function helpAction($argument = null)
    {

    }

    public function requestAccessAction()
    {
        $this->queryParameters = $this->_getRequest()->getQueryParameters();
    }

    public function performRequestAccessAction()
    {
        if (!$this->_requiredDataValid(array("name", "email", "comment"))) {
            $this->queryParameters = $_POST;
            $this->renderAction("RequestAccess");
        } else {
            $this->_sendRequestAccessMail(
                urldecode($_POST['idpEntityId']),
                urldecode($_POST['spEntityId']),
                $_POST['name'],
                $_POST['email'],
                $_POST['comment'] );
        }
    }

    public function certificateAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
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
}
