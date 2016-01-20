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

        if (!stream_is_local($file)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s"', $file));
        }

        if (!file_exists($file)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist', $file));
        }

        $parsedFile = parse_ini_file($file, true);

        if ($parsedFile === false || $parsedFile === array()) {
            throw new InvalidArgumentException(sprintf('The "%s" file is not valid', $file));
        }

        return $parsedFile;
    }
}

