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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use PHPUnit\Framework\TestCase;

class EngineBlock_Corto_Model_Consent_Test extends TestCase
{
    private $consentDisabled;
    private $consent;
    private $mockedDatabaseConnection;
    private $mockedStatement;

    public function setUp(): void
    {
        $this->mockedDatabaseConnection = Phake::mock('EngineBlock_Database_ConnectionFactory');
        $dbConnection = Phake::mock(Connection::class);
        $this->mockedStatement = Phake::mock(Statement::class);
        Phake::when($this->mockedDatabaseConnection)
            ->getConnection()
            ->thenReturn($dbConnection);
        Phake::when($dbConnection)
            ->prepare(Phake::anyParameters())
            ->thenReturn($this->mockedStatement);
        Phake::when($this->mockedStatement)
            ->execute(Phake::anyParameters())
            ->thenReturn(true);
        Phake::when($this->mockedStatement)
            ->fetchAll()
            ->thenReturn([1, 2]);
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

        Phake::verify($this->mockedStatement, Phake::times(0))->execute(Phake::anyParameters());
    }

    public function testConsentWriteToDatabase()
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->consent->explicitConsentWasGivenFor($serviceProvider);
        $this->consent->implicitConsentWasGivenFor($serviceProvider);
        $this->consent->giveExplicitConsentFor($serviceProvider);
        $this->consent->giveImplicitConsentFor($serviceProvider);

        Phake::verify($this->mockedStatement, Phake::times(4))->execute(Phake::anyParameters());
    }
}
