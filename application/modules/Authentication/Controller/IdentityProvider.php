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
    public function singleSignOnAction($argument = null)
    {
        $this->setNoRender();
        $application = EngineBlock_ApplicationSingleton::getInstance();

        try {

            $proxyServer = new EngineBlock_Corto_Adapter();

            $idPEntityId = NULL;

            if (substr($argument, 0, 3) == "vo:") {
                $proxyServer->setVirtualOrganisationContext(substr($argument, 3));
            } else {
                $idPEntityId = $argument;
            }

            $proxyServer->singleSignOn($idPEntityId);
        }
        catch (Corto_Module_Bindings_UnableToReceiveMessageException $e) {
            $application->getLog()->warn('Unable to receive message');
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/unable-to-receive-message');
        }
        catch (EngineBlock_Corto_Exception_UserNotMember $e) {
            $application->getLog()->warn('User not a member error');
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/vomembershiprequired');
        }
        catch (Corto_Module_Services_SessionLostException $e) {
            $application->getLog()->warn('Session was lost');
            $application->getHttpResponse()->setRedirectUrl('/authentication/feedback/session-lost');
        }
        catch (EngineBlock_Corto_Exception_UnknownIssuerException $e) {
            $application->getLog()->warn($e->getMessage());
            $application->getHttpResponse()->setRedirectUrl(
                '/authentication/feedback/unknown-issuer?entity-id=' . urlencode($e->getEntityId()) .
                '&destination=' . urlencode($e->getDestination())
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

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processConsent();
    }

    public function helpAction($argument = null)
    {

    }
}
