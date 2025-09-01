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
use Twig\TwigFunction;

/**
 * The debug extension is used to provide var_dump, var_export and print_r functions for usage in Twig templates.
 */
class Debug extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('var_export', [$this, 'varExport']),
            new TwigFunction('print_r', [$this, 'printHumanReadable']),
        ];
    }

    /**
     * Provide var export functionality for use in Twig templates
     * @param mixed $expression
     * @return string
     */
    public function varExport($expression)
    {
        return var_export($expression, true);
    }

    /**
     * Returns the output of print_r with the added instruction to return the output as a string.
     * @param mixed $expression
     * @return string
     */
    public function printHumanReadable($expression)
    {
        return print_r($expression, true);
    }
}
