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

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

class EngineBlock_Exception_UnknownServiceProvider extends \Exception
{
    /**
     * @var ServiceProvider
     */
    private $trustedProxyServiceProvider;

    /**
     * @var string
     */
    private $requesterEntityId;

    /**
     * EngineBlock_Exception_UnknownServiceProvider constructor.
     *
     * @param ServiceProvider $trustedProxyServiceProvider
     * @param string          $requesterEntityId
     */
    public function __construct(
        ServiceProvider $trustedProxyServiceProvider,
        $requesterEntityId
    ) {
        $this->trustedProxyServiceProvider = $trustedProxyServiceProvider;
        $this->requesterEntityId           = $requesterEntityId;
    }
}
