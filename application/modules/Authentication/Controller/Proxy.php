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

class Authentication_Controller_Proxy extends EngineBlock_Controller_Abstract
{
    /**
     *
     *
     * @param string $encodedIdPEntityId
     * @return void
     */
    public function idPsMetaDataAction($argument = "")
    {
        $this->setNoRender();

        $application = EngineBlock_ApplicationSingleton::getInstance();

        $queryString = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryString();
        $proxyServer = new EngineBlock_Corto_Adapter();
        try {
            if (substr($argument, 0, 3) == "vo:") {
                $proxyServer->setVirtualOrganisationContext(substr($argument, 3));
            } else if (!empty($argument)) {
                throw new EngineBlock_Exception("Unknown argument", EngineBlock_Exception::CODE_NOTICE);
            }

            $proxyServer->idPsMetadata();
        } catch(EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unknown-service-provider?entity-id=' . urlencode($e->getEntityId())
            );
        }
    }

    public function edugainMetaDataAction()
    {
        $this->setNoRender();

        $application = EngineBlock_ApplicationSingleton::getInstance();

        $queryString = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryString();
        $proxyServer = new EngineBlock_Corto_Adapter();
        try {
            $proxyServer->edugainMetadata($queryString);
        } catch(EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException $e) {
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/unknown-service-provider?entity-id=' . urlencode($e->getEntityId())
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
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/vomembershiprequired');
        }
        catch (EngineBlock_Attributes_Manipulator_CustomException $e) {
            $_SESSION['feedback_custom'] = $e->getFeedback();
            $application->handleExceptionWithFeedback($e,
                '/authentication/feedback/custom');
        }
    }
}
