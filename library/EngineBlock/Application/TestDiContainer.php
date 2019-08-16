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

use OpenConext\EngineBlockBundle\Stepup\StepupEndpoint;

/**
 * Creates mocked versions of dependencies for unit testing
 */
class EngineBlock_Application_TestDiContainer extends EngineBlock_Application_DiContainer
{
    public function getXmlConverter()
    {
        return Phake::mock('EngineBlock_Corto_XmlToArray');
    }

    public function getFilterCommandFactory()
    {
        return Phake::mock('EngineBlock_Corto_Filter_Command_Factory');
    }

    public function getDatabaseConnectionFactory()
    {
        return Phake::mock('EngineBlock_Database_ConnectionFactory');
    }

    public function getConsentFactory()
    {
        $consentFactoryMock = Phake::mock('EngineBlock_Corto_Model_Consent_Factory');

        Phake::when($consentFactoryMock)
            ->create(Phake::anyParameters())
            ->thenReturn(Phake::mock('EngineBlock_Corto_Model_Consent'));

        return $consentFactoryMock;
    }

    /**
     * @return EngineBlock_Attributes_Metadata
     */
    public function getAttributeMetadata()
    {
        // returns a realistic representation of the attribute metadata
        $definitions = json_decode(file_get_contents(__DIR__ . '/../../../tests/resources/config/attributes-fixture.json'), true);
        return new EngineBlock_Attributes_Metadata($definitions, Phake::mock('\Psr\Log\LoggerInterface'));
    }

    /**
     * @return array
     */
    public function getEncryptionKeysConfiguration()
    {
        $basePath = $this->container->getParameter('kernel.project_dir');

        return [
            'default' => [
                'publicFile' => $basePath . '/tests/resources/key/engineblock.crt',
                'privateFile' => $basePath . '/tests/resources/key/engineblock.pem',
            ],
        ];
    }
}
