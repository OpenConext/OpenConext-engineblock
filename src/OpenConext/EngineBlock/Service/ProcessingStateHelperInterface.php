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
use EngineBlock_Saml2_ResponseAnnotationDecorator;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Service\Dto\ProcessingStateStep;

/**
 * Used to manage the process state for Stepup authentication and Consent flows
 */
interface ProcessingStateHelperInterface
{
    const STEP_CONSENT = 'consent';
    const STEP_STEPUP = 'stepup';
    const STEP_SRAM = 'sram';

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
    );

    /**
     * @param string $requestId
     * @param string $name
     * @return ProcessingStateStep
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     */
    public function getStepByRequestId($requestId, $name);

    public function hasStepRequestById(string $requestId, string $name): bool;

    /**
     * @param $name
     * @param $requestId
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @return ProcessingStateStep
     */
    public function updateStepResponseByRequestId(
        $name,
        $requestId,
        EngineBlock_Saml2_ResponseAnnotationDecorator $response
    );

    /**
     * @param string $requestId
     * @return void
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     */
    public function clearStepByRequestId($requestId);
}
