<?php

/**
 * Copyright 2015 SURFnet bv
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

namespace OpenConext\EngineBlock\AuthenticationBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use EngineBlock_View;

class ServiceProviderController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView = $engineBlockView;
    }

    public function consumeAssertionAction()
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        $proxyServer = new EngineBlock_Corto_Adapter();

        try {
            $proxyServer->consumeAssertion();
        } catch (EngineBlock_Corto_Exception_UserNotMember $e) {
            $application->getLogInstance()->notice(
                "VO membership required",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/vomembershiprequired'
            );
        } catch (EngineBlock_Corto_Module_Services_SessionLostException $e) {
            $application->getLogInstance()->notice(
                "Session Lost",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/session-lost'
            );
        } catch (EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException $e) {
            $application->getLogInstance()->notice(
                "Unable to receive message",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/unable-to-receive-message'
            );
        } catch (EngineBlock_Corto_Exception_UnknownIssuer $e) {
            $application->getLogInstance()->notice(
                "Unknown Issuer",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/unknown-issuer?entity-id=' . urlencode($e->getEntityId()) .
                '&destination=' . urlencode($e->getDestination())
            );
        } catch (EngineBlock_Corto_Exception_MissingRequiredFields $e) {
            $application->getLogInstance()->notice(
                "Missing Required Fields",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/missing-required-fields'
            );
        } catch (EngineBlock_Attributes_Manipulator_CustomException $e) {
            $_SESSION['feedback_custom'] = $e->getFeedback();
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/custom'
            );
        } catch (EngineBlock_Corto_Module_Bindings_UnsupportedBindingException $e) {
            $application->getLogInstance()->notice(
                "Unsupported Binding",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/invalid-acs-binding'
            );
        } catch (EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e) {
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
        } catch (EngineBlock_Corto_Module_Bindings_SignatureVerificationException $e) {
            $application->getLogInstance()->warning(
                "Unable to verify signature, cert wrong?",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/received-invalid-signed-response'
            );
        } catch (EngineBlock_Corto_Module_Bindings_VerificationException $e) {
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
}
