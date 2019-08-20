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

namespace OpenConext\EngineBlockBundle\Tests;

use OpenConext\EngineBlockBundle\Value\FeedbackInformation;
use PHPUnit_Framework_TestCase as TestCase;

class FeedbackInformationTest extends TestCase
{
    /**
     * @dataProvider attributePredictions
     */
    public function test_attr_safe_key_is_returned($identifier, $input, $expectation)
    {
        $feedbackInfo = new FeedbackInformation($input, 'Test value');
        $safeKey = $feedbackInfo->getAttrSafeKey();
        $this->assertEquals(
            $expectation,
            $safeKey,
            sprintf('Converted an unexpected HTML attribute safe key "%s"', $identifier)
        );
    }

    public function attributePredictions()
    {
        return [
            ['with whitespace', 'my attribute', 'my-attribute'],
            ['convert to lowercase', 'ATTRIBUTE', 'attribute'],
            ['converts characters', 'alert("hello-world");', 'alert(&quot;hello-world&quot;);'],
        ];
    }
}
