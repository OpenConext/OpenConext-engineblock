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

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Custom replacement for the deprecated core Twig 'spaceless' filter (deprecated as of Twig 3.12).
 *
 * Keeps existing templates that use `{% apply spaceless %}` or `|spaceless` working without triggering
 * the core deprecation by overriding the filter with our own implementation.
 *
 * Behavior intentionally mimics legacy implementation: it removes any whitespace characters that appear
 * between a closing angle bracket and an opening angle bracket (">   <" => "><"). Whitespace inside
 * tags or textual content is preserved.
 */
class Spaceless extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('spaceless', [$this, 'spaceless'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Removes whitespaces between HTML/XML tags.
     * Mirrors the prior (now deprecated) Twig spaceless filter behavior.
     */
    public function spaceless(?string $content): string
    {
        if ($content === null) {
            return '';
        }
        return trim(preg_replace('/>\s+</', '><', $content));
    }
}

