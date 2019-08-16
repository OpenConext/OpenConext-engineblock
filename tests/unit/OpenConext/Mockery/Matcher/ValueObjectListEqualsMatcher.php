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

namespace OpenConext\Mockery\Matcher;

use InvalidArgumentException;
use Mockery\Matcher\MatcherAbstract;

final class ValueObjectListEqualsMatcher extends MatcherAbstract
{
    /**
     * @param object[] $valueObjects An array of value objects of the same class, implementing an equals() method
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $valueObjects)
    {
        $message = sprintf(
            'Argument 1 passed to %s must be an array of value objects of the same class, implementing an equals() method',
            __METHOD__
        );

        $first = array_values($valueObjects)[0];

        if (!is_object($first) || !method_exists($first, 'equals')) {
            throw new InvalidArgumentException($message);
        }

        foreach ($valueObjects as $valueObject) {
            if (!is_object($valueObject) || get_class($valueObject) !== get_class($first)) {
                throw new InvalidArgumentException($message);
            }
        }

        parent::__construct($valueObjects);
    }

    /**
     * @param array $actual
     *
     * @return bool
     */
    public function match(&$actual)
    {
        if (!is_array($actual) || count($actual) !== count($this->_expected)) {
            return false;
        }

        foreach ($actual as $index => $valueObject) {
            if (get_class($valueObject) !== get_class($this->_expected[$index])) {
                return false;
            }

            if (!$valueObject->equals($this->_expected[$index])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('<ValueObjectListEqualsMatcher([%s])>', implode(', ', $this->_expected));
    }
}
