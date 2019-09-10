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

namespace OpenConext\Mockery\Matcher;

use Mockery\Exception\RuntimeException;
use Mockery\Matcher\MatcherAbstract;

/**
 * A Mockery matcher that can be used to verify equality of value objects
 */
class ValueObjectEqualsMatcher extends MatcherAbstract
{
    public function __construct($expected)
    {
        if (!is_object($expected) || !method_exists($expected, 'equals')) {
            throw new RuntimeException(
                'In order to use the ValueObjectEqualsMatcher an object that implements "equals" method to compare'
                . ' itself against an instance of itself should be given.'
            );
        }

        parent::__construct($expected);
    }

    public function match(&$actual)
    {
        if (get_class($actual) !== get_class($this->_expected)) {
            return false;
        }

        return $this->_expected->equals($actual);
    }

    public function __toString()
    {
        return sprintf('<ValueObjectEqualsMatcher(%s)>', $this->_expected);
    }
}
