<?php

namespace OpenConext\EngineBlock\AuthenticationBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_View;

class ConsentController
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

    public function processConsentAction()
    {
        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processConsent();

//        try {
//
//        } catch (EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException $e) {
//            $application->handleExceptionWithFeedback(
//                $e,
//                '/authentication/feedback/unable-to-receive-message'
//            );
//        } catch (EngineBlock_Corto_Exception_UserNotMember $e) {
//            $application->handleExceptionWithFeedback(
//                $e,
//                '/authentication/feedback/vomembershiprequired'
//            );
//        } catch (EngineBlock_Corto_Module_Services_SessionLostException $e) {
//            $application->handleExceptionWithFeedback(
//                $e,
//                '/authentication/feedback/session-lost'
//            );
//        } catch (EngineBlock_Corto_Exception_UnknownIssuer $e) {
//            $application->handleExceptionWithFeedback(
//                $e,
//                '/authentication/feedback/unknown-issuer?entity-id=' . urlencode($e->getEntityId()) .
//                '&destination=' . urlencode($e->getDestination())
//            );
//        } catch (EngineBlock_Attributes_Manipulator_CustomException $e) {
//            $_SESSION['feedback_custom'] = $e->getFeedback();
//            $application->handleExceptionWithFeedback(
//                $e,
//                '/authentication/feedback/custom'
//            );
//        } catch (EngineBlock_Corto_Exception_NoConsentProvided $e) {
//            $application->handleExceptionWithFeedback(
//                $e,
//                '/authentication/feedback/no-consent'
//            );
//        }
    }

    public function helpConsentAction($argument = null)
    {
        // the arguments don't seem to be used?
    }
}
