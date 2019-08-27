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

namespace OpenConext\EngineBlockBridge\Configuration;

use InvalidArgumentException;
use OpenConext\EngineBlock\Assert\Assertion;

final class EngineBlockIniFileLoader
{
    const CONFIG_FILE_DEFAULT       = 'configs/application.ini';
    const CONFIG_FILE_ENVIRONMENT   = '/etc/openconext/engineblock.ini';

    /**
     * @param string[] $files
     * @return array
     */
    public function load(array $files)
    {
        $parsedValues = [];
        foreach ($files as $file) {
            $parsedValues = array_replace_recursive($parsedValues, $this->parseIniFile($file));
        }

        $mappedValues = [];
        foreach ($parsedValues as $keys => $value) {
            // In order to prevent Symfony parsing percent signs as parameter references, they are escaped
            $value = str_replace('%', '%%', $value);

            $mappedValues = $this->map($value, explode('.', $keys), $mappedValues);
        }

        return $mappedValues;
    }

    /**
     * @param mixed $value
     * @param array $keys
     * @param array $mappedValues
     * @return array
     */
    private function map($value, array $keys, array $mappedValues)
    {
        if (count($keys) > 1) {
            return $this->map([array_pop($keys) => $value], $keys, $mappedValues);
        }

        return array_replace_recursive($mappedValues, [$keys[0] => $value]);
    }

    /**
     * @param string $file
     * @return array
     */
    private function parseIniFile($file)
    {
        Assertion::nonEmptyString($file, 'file');

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf(
                'Could not parse given ini file: file "%s" does not exist',
                $file
            ));
        }

        if (!is_readable($file)) {
            throw new InvalidArgumentException(sprintf(
                'Could not parse given ini file: file "%s" is not readable',
                $file
            ));
        }

        $parsedFile = parse_ini_file($file);

        if ($parsedFile === false) {
            throw new InvalidArgumentException(sprintf(
                'Could not parse given ini file: file "%s" is not valid',
                $file
            ));
        }

        return $parsedFile;
    }
}
