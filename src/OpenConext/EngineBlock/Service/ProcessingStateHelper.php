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

namespace OpenConext\EngineBlock\Service;

use EngineBlock_Corto_Module_Services_SessionLostException;
use EngineBlock_Corto_Module_Services_Exception;
use EngineBlock_Saml2_ResponseAnnotationDecorator;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Service\Dto\ProcessingStateStep;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProcessingStateHelper implements ProcessingStateHelperInterface
{

    const SESSION_KEY = 'Processing';

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    /**
     * @param string $requestId
     * @param string $name
     * @param AbstractRole $role
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @return ProcessingStateStep
     */
    public function addStep(
        $requestId,
        $name,
        AbstractRole $role,
        EngineBlock_Saml2_ResponseAnnotationDecorator $response
    ) {
        // Add the additional checks to the session
        $processingStep = new ProcessingStateStep($response, $role);
        $processing = $this->session->get(self::SESSION_KEY);
        $processing[$requestId][$name] = $processingStep;
        $this->session->set(self::SESSION_KEY, $processing);

        return $processingStep;
    }

    /**
     * @param string $requestId
     * @param string $name
     * @return ProcessingStateStep
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     * @throws EngineBlock_Corto_Module_Services_Exception
     */
    public function getStepByRequestId($requestId, $name)
    {
        $processing = $this->session->get(self::SESSION_KEY);
        if (empty($processing)) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException('Session lost after consent');
        }
        if (!isset($processing[$requestId])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                sprintf('Stored response for ResponseID "%s" not found', $requestId)
            );
        }
        if (!isset($processing[$requestId][$name])) {
            throw new EngineBlock_Corto_Module_Services_Exception(
                sprintf('Process step requested for ResponseID "%s" not found', $requestId)
            );
        }

        return $processing[$requestId][$name];
    }


    /**
     * @param string $name
     * @param string $requestId
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @return ProcessingStateStep
     * @throws EngineBlock_Corto_Module_Services_Exception
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     */
    public function updateStepResponseByRequestId(
        $requestId,
        $name,
        EngineBlock_Saml2_ResponseAnnotationDecorator $response
    ) {
        $processing = $this->session->get(self::SESSION_KEY);
        $processingStep = $this->getStepByRequestId($requestId, $name);
        $updatedProcessingStep = new ProcessingStateStep($response, $processingStep->getRole());
        $processing[$requestId][$name] = $updatedProcessingStep;
        $this->session->set(self::SESSION_KEY, $processing);

        return $updatedProcessingStep;
    }

    /**
     * @param string $requestId
     * @return void
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     */
    public function clearStepByRequestId($requestId)
    {
        $processing = $this->session->get(self::SESSION_KEY);
        if (empty($processing)) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException('Session lost after consent');
        }
        if (!isset($processing[$requestId])) {
            throw new EngineBlock_Corto_Module_Services_SessionLostException(
                sprintf('Stored response for ResponseID "%s" not found', $requestId)
            );
        }

        unset($processing[$requestId]);

        $this->session->set(self::SESSION_KEY, $processing);
    }
}
