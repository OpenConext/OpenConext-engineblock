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
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/vomembershiprequired');
        }
        catch (EngineBlock_Corto_Module_Services_SessionLostException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/session-lost');
        }
        catch (EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unable-to-receive-message');
        }
        catch (EngineBlock_Corto_Exception_UnknownIssuer $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unknown-issuer?entity-id='.urlencode($e->getEntityId()).'&destination='.urlencode($e->getDestination()));
        }
        catch (EngineBlock_Corto_Exception_MissingRequiredFields $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/missing-required-fields');
        }
        catch (EngineBlock_Attributes_Manipulator_CustomException $e) {
            $_SESSION['feedback_custom'] = $e->getFeedback();
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/custom');
        } catch (EngineBlock_Corto_Module_Bindings_UnsupportedBindingException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/invalid-acs-binding');
        } catch (EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e) {
            // Add extra feedback info
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/received-error-status-code',
                $e->getFeedbackInfo());
        } catch (EngineBlock_Corto_Module_Bindings_VerificationException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/received-invalid-response');
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
        $proxyServer->sPMetadata();
    }

    public function certificateAction()
    {
        $this->setNoRender();

        $proxyServer = new EngineBlock_Corto_Adapter();
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
