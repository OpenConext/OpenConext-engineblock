<?php

/**
 * Copyright 2010 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlock\Validator\RequestValidator;
use OpenConext\EngineBlockBridge\ResponseFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class StepupController implements AuthenticationLoopThrottlingController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @var RequestValidator
     */
    private $bindingValidator;

    /**
     * @var RequestValidator
     */
    private $responseValidator;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        RequestValidator $requestValidator,
        RequestValidator $bindingValidator,
        RequestValidator $samlResponseValidator
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->requestValidator = $requestValidator;
        $this->bindingValidator = $bindingValidator;
        $this->responseValidator = $samlResponseValidator;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function consumeAssertionAction(Request $request)
    {
        $this->requestValidator->isValid($request);
        $this->bindingValidator->isValid($request);
        $this->responseValidator->isValid($request);

        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->stepupConsumeAssertion();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
