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

namespace OpenConext\EngineBlockBundle\Twig\Extensions\Extension;

use PHPUnit\Framework\TestCase;

class SpacelessTest extends TestCase
{
    private Spaceless $extension;

    protected function setUp(): void
    {
        $this->extension = new Spaceless();
    }

    public function testItRemovesWhitespaceBetweenTags(): void
    {
        $input = "<div>\n   </div>   <span>  text </span>";
        $expected = '<div></div><span>  text </span>';
        $this->assertSame($expected, $this->extension->spaceless($input));
    }

    public function testItReturnsEmptyStringForNull(): void
    {
        $this->assertSame('', $this->extension->spaceless(null));
    }

    public function testItTrimsOuterWhitespace(): void
    {
        $input = "   <p>a</p>   <p>b</p>   ";
        $expected = '<p>a</p><p>b</p>';
        $this->assertSame($expected, $this->extension->spaceless($input));
    }
}

