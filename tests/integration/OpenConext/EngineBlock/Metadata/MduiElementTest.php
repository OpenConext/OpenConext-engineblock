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

namespace OpenConext\EngineBlock\Tests;

use OpenConext\EngineBlock\Metadata\MduiElement;
use OpenConext\EngineBlock\Metadata\MultilingualValue;
use PHPUnit\Framework\TestCase;

class MduiElementTest extends TestCase
{
    private $element;

    protected function setUp(): void
    {
        $displayNames = [
            new MultilingualValue('Cornelius Hill fantastic SP', 'en'),
            new MultilingualValue('Cornelius Hill fantastische SP', 'nl'),
        ];
        $this->element = new MduiElement('DisplayName', $displayNames);
    }

    public function test_get_value_by_language()
    {
        $displayNameEn = $this->element->translate('en');
        $displayNameNl = $this->element->translate('nl');

        $this->assertEquals('Cornelius Hill fantastic SP', $displayNameEn->getValue());
        $this->assertEquals('Cornelius Hill fantastische SP', $displayNameNl->getValue());
    }
    public function test_json_serialization()
    {
        $serialized = json_encode($this->element);
        $this->assertEquals(
            '{"name":"DisplayName","values":{"en":{"value":"Cornelius Hill fantastic SP","language":"en"},"nl":{"value":"Cornelius Hill fantastische SP","language":"nl"}}}',
            $serialized
        );
    }
}
