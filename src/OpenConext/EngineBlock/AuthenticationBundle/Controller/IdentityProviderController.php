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
use Exception;
use OpenConext\EngineBlock\CompatibilityBundle\Bridge\ResponseFactory;
use Psr\Log\LoggerInterface;

class IdentityProviderController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView,
        LoggerInterface $loggerInterface
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView                 = $engineBlockView;
        $this->logger                          = $loggerInterface;
    }

    /**
     * @param null $virtualOrganization
     * @param null $keyId
     * @param null $idpHash
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Exception
     */
    public function singleSignOnAction($virtualOrganization = null, $keyId = null, $idpHash = null)
    {
        $cortoAdapter = new EngineBlock_Corto_Adapter();

        if ($virtualOrganization !== null) {
            $cortoAdapter->setVirtualOrganisationContext($virtualOrganization);
        }

        if ($keyId !== null) {
            $cortoAdapter->setKeyId($keyId);
        }

        try {
            $cortoAdapter->singleSignOn($idpHash);
        } catch (Exception $e) {
            $this->logger->critical(sprintf(
                'Could not process Single Sign On Request, exception "%s[%d]" thrown'
            ));

            // we let the exception handler pick it up from here
            throw $e;
        }

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    public function unsolicitedSingleSignOnAction($virtualOrganization = null, $keyRollover = null, $idpHash = null)
    {

    }
}
