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

use OpenConext\EngineBlock\Stepup\StepupEndpoint;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\FakeUserDirectory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Creates mocked versions of dependencies for functional testing
 */
class EngineBlock_Application_FunctionalTestDiContainer extends EngineBlock_Application_DiContainer
{
    public function getUserDirectory()
    {
        return new FakeUserDirectory(new Filesystem());
    }

    public function getFeatureConfiguration()
    {
        return $this->getSymfonyContainer()->get('engineblock.functional_testing.fixture.features');
    }

    public function getAuthenticationLoopGuard()
    {
        return $this->getSymfonyContainer()->get('engineblock.functional_testing.fixture.authentication_loop_guard');
    }

    public function getPdpClient()
    {
        return $this->getFunctionalTestingPdpClient();
    }

    public function getPdpClientId()
    {
        return 'Federation';
    }

    /**
     * @return \OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface
     */
    public function getAttributeAggregationClient()
    {
        return $this->getSymfonyContainer()->get('engineblock.functional_testing.fixture.attribute_aggregation_client');
    }

    public function getAuthnContextClassRefBlacklistRegex()
    {
        return '/invalid-authn-context-class-ref/';
    }

    /**
     * This method is used to mock the private key and will return the same private key as in /etc/openconext/engineblock.pem
     * The file /etc/openconext/engineblock.pem is not readable for all users and would break running tests because the
     * key file could not be opened so therefore a different file is used.
     * The private key is needed to validate stepup authentication responses which will use the same key for the gateway
     *
     * @return array
     */
    public function getEncryptionKeysConfiguration()
    {
        $basePath = $this->container->getParameter('kernel.project_dir');
        return [
            'default' => [
                'publicFile' => '/config/engine/engineblock.crt',
                'privateFile' => $basePath . '/ci/qa-config/files/engineblock.pem',
            ],
        ];
    }
}
