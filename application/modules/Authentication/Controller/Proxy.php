<?php

class Authentication_Controller_Proxy extends EngineBlock_Controller_Abstract
{
    public function idPsMetaDataAction()
    {
        $this->setNoRender();

        $application = EngineBlock_ApplicationSingleton::getInstance();

        $proxyServer = new EngineBlock_Corto_Adapter();
        try {
            foreach (func_get_args() as $argument) {
                if (substr($argument, 0, 3) === 'vo:') {
                    $proxyServer->setVirtualOrganisationContext(substr($argument, 3));
                } else if (substr($argument, 0, 4) === 'key:') {
                    $proxyServer->setKeyId(substr($argument, 4));
                } else {
                    $application->getLogInstance()->notice(
                        "Ignoring unknown argument '$argument'."
                    );
                }
            }
            $proxyServer->idPsMetadata();
        }
        catch(EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException $e) {
            $application->getLogInstance()->notice(
                "Unknown remote entity: " . $e->getEntityId(),
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unknown-service-provider?entity-id=' . urlencode($e->getEntityId()));
        }
        catch(Janus_Client_CacheProxy_Exception $e) {
            $application->getLogInstance()->notice(
                "Unknown Service Provider?",
                array('exception' => $e)
            );
            $spEntityId = $application->getHttpRequest()->getQueryParameter('sp-entity-id');
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unknown-service-provider?entity-id=' . urlencode($spEntityId));
        }
    }

    public function edugainMetaDataAction()
    {
        $this->setNoRender();

        $application = EngineBlock_ApplicationSingleton::getInstance();

        $queryString = $application->getHttpRequest()->getQueryString();
        $proxyServer = new EngineBlock_Corto_Adapter();

        foreach (func_get_args() as $argument) {
            if (substr($argument, 0, 4) === 'key:') {
                $proxyServer->setKeyId(substr($argument, 4));
            } else {
                $application->getLogInstance()->notice(
                    "Ignoring unknown argument '$argument'."
                );
            }
        }

        try {
            $proxyServer->edugainMetadata($queryString);
        }
        catch(EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException $e) {
            $application->getLogInstance()->notice(
                "Unknown Service Provider?",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/unknown-service-provider?entity-id=' . urlencode($e->getEntityId())
            );
        }
        catch(Janus_Client_CacheProxy_Exception $e) {
            $spEntityId = $application->getHttpRequest()->getQueryParameter('sp-entity-id');
            $application->getLogInstance()->notice(
                "Unknown Service Provider '$spEntityId'?",
                array('exception' => $e)
            );
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/unknown-service-provider?entity-id=' . urlencode($spEntityId)
            );
        }
    }

    public function processedAssertionAction()
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();
        try {
            $proxyServer = new EngineBlock_Corto_Adapter();
            $proxyServer->processedAssertionConsumer();
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
        catch (EngineBlock_Attributes_Manipulator_CustomException $e) {
            $application->getLogInstance()->notice(
                "Custom attribute manipulator exception",
                array('exception' => $e)
            );
            $_SESSION['feedback_custom'] = $e->getFeedback();
            $application->handleExceptionWithFeedback(
                $e,
                '/authentication/feedback/custom'
            );
        }
    }
}
