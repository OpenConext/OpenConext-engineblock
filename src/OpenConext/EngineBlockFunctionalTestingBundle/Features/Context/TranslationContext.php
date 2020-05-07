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

use Behat\Gherkin\Node\TableNode;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockTranslator;

/**
 *
 */
class TranslationContext extends AbstractSubContext
{
    /**
     * @var MockTranslator
     */
    private $mockTranslator;

    /**
     * @AfterScenario
     */
    public function cleanUp()
    {
        $this->mockTranslator->clear();
    }

    /**
     * @param MockTranslator $mockTranslator
     */
    public function __construct(MockTranslator $mockTranslator)
    {

        $this->mockTranslator = $mockTranslator;
    }

    /**
     * @Then /^I have configured the following translations:$/
     */
    public function iHaveConfiguredTheFollowingTranslations(TableNode $translations)
    {
        foreach ($translations->getHash() as $translation) {
            $this->mockTranslator->setTranslation($translation['Key'], $translation['Value']);
        }
    }
}
