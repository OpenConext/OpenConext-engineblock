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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Class AbstractSubContext
 */
abstract class AbstractSubContext implements Context
{
    /**
     * @var MinkContext
     */
    protected $minkContext;

    const SESSION_DEFAULT = 'default';
    const SESSION_CHROME = 'chrome';

    // Usefull for step debugging in the executed code
//    /**
//     * @beforeStep
//     *
//     * @param BeforeStepScope $scope
//     */
//    public function putDebugCookie(BeforeStepScope $scope)
//    {
//        $driver = $this->getMinkContext()->getSession()->getDriver();
//        $driver->setCookie('XDEBUG_SESSION', 'PHPSTORM');
//    }

    /**
     * @BeforeScenario
     */
    public function prepareContext(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->minkContext = $environment->getContext(MinkContext::class);
    }

    public function getMinkContext()
    {
        return $this->minkContext;
    }
}
