<?php

namespace OpenConext\EngineBlock\CompatibilityBundle\Configuration;

use OpenConext\EngineBlock\CompatibilityBundle\Exception\InvalidArgumentException;

final class EngineBlockIniFileLoader
{
    /**
     * @param string[] $files
     * @return array
     */
    public function load(array $files)
    {
        $parsedValues = array();
        foreach ($files as $file) {
            $parsedValues = array_replace_recursive($parsedValues, $this->parseIniFile($file));
        }

        $mappedValues = array();
        foreach($parsedValues as $keys => $value) {
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
            return $this->map(array(array_pop($keys) => $value), $keys, $mappedValues);
        }

        // In order to prevent Symfony parsing percent signs as parameter references, they are escaped
        $valueWithoutParameters = str_replace('%', '%%', $value);

        return array_replace_recursive($mappedValues, array($keys[0] => $valueWithoutParameters));
    }
    
    /**
     * @param string $file
     * @return array
     */
    private function parseIniFile($file)
    {
        if (!is_string($file) || trim($file) === '' ) {
            throw InvalidArgumentException::invalidType('non-empty string', 'file', $file);
        }

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

        $parsedFile = parse_ini_file($file, true);

        if ($parsedFile === false) {
            throw new InvalidArgumentException(sprintf(
                'Could not parse given ini file: file "%s" is not valid',
                $file
            ));
        }

        return $parsedFile;
    }
}

