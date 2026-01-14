<?php

/**
 * Copyright 2025 SURFnet B.V.
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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Controllers;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\SbsClientStateManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SBS is a membership management portal.
 * It allows user to define their own collaborations (rich groups), invite new users to those groups and give this collaboration access to services.
 *
 * This controller acts as an SBS mock.
 */
class SbsController extends AbstractController
{

    /**
     * @var SbsClientStateManager
     */
    private $sbsClientStateManager;

    /**
     * @var AbstractDataStore
     */
    private $dataStore;

    public function __construct(
        SbsClientStateManager $sbsClientStateManager,
        AbstractDataStore $dataStore
    ) {
        $this->sbsClientStateManager = $sbsClientStateManager;
        $this->dataStore = $dataStore;
    }

    /**
     * The endpoint Engine calls to see if the user is 'known' in SBS
     */
    public function authzAction(Request $request): JsonResponse
    {
        $this->dataStore->save(json_decode($request->getContent(), true));
        return new JsonResponse($this->sbsClientStateManager->getPreparedAuthzResponse());
    }

    /**
     * The endpoint the browser is redirected to if the user is 'unknown' in SBS
     */
    public function interruptAction(Request $request): Response
    {
        $storedData = $this->dataStore->load();
        $returnUrl = $storedData['continue_url'];

        // url contains the ID=<ID>, so the session is preserved
        return new Response(sprintf(
            '<html><body><a href="%s">Continue</a></body></html>',
            $returnUrl
        ));
    }

    /**
     * The endpoint called by Engine to fetch the attributes from SBS, to be merged into the SAML request
     * This gets called after the interrupt, or when the user is already authorized in SBS.
     */
    public function attributesAction()
    {
        return new JsonResponse($this->sbsClientStateManager->getPreparedAttributesResponse());
    }
}
