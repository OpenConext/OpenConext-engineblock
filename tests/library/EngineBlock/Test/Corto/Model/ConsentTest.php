<?php

/**
 * Copyright 2021 Stichting Kennisnet
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
use PHPUnit\Framework\TestCase;

class EngineBlock_Corto_Model_Consent_Test extends TestCase
{
    private $consentDisabled;
    private $consent;
    private $mockedDatabaseConnection;

    public function setup()
    {
        $this->mockedDatabaseConnection = Phake::mock('EngineBlock_Database_ConnectionFactory');
        $mockedResponse = Phake::mock('EngineBlock_Saml2_ResponseAnnotationDecorator');

        $this->consentDisabled = new EngineBlock_Corto_Model_Consent(
            "consent",
            true,
            $mockedResponse,
            [],
            $this->mockedDatabaseConnection,
            false,
            false
        );

        $this->consent = new EngineBlock_Corto_Model_Consent(
            "consent",
            true,
            $mockedResponse,
            [],
            $this->mockedDatabaseConnection,
            false,
            true
        );
    }

    public function testConsentDisabledDoesNotWriteToDatabase()
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->consentDisabled->explicitConsentWasGivenFor($serviceProvider);
        $this->consentDisabled->implicitConsentWasGivenFor($serviceProvider);
        $this->consentDisabled->giveExplicitConsentFor($serviceProvider);
        $this->consentDisabled->giveImplicitConsentFor($serviceProvider);

        Phake::verify($this->mockedDatabaseConnection, Phake::times(0))->create();
    }

    public function testConsentWriteToDatabase()
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->consent->explicitConsentWasGivenFor($serviceProvider);
        $this->consent->implicitConsentWasGivenFor($serviceProvider);
        $this->consent->giveExplicitConsentFor($serviceProvider);
        $this->consent->giveImplicitConsentFor($serviceProvider);

        Phake::verify($this->mockedDatabaseConnection, Phake::times(4))->create();
    }
}
