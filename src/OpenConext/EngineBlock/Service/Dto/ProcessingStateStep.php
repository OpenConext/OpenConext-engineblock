<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Service\Dto;

use EngineBlock_Saml2_ResponseAnnotationDecorator;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProcessingStateStep
{
    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    private $response;
    /**
     * @var AbstractRole
     */
    private $role;

    /**
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @param AbstractRole $role
     */
    public function __construct(
        EngineBlock_Saml2_ResponseAnnotationDecorator $response,
        AbstractRole $role
    ) {
        $this->response = $response;
        $this->role = $role;
    }

    /**
     * @return EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return AbstractRole
     */
    public function getRole()
    {
        return $this->role;
    }
}
