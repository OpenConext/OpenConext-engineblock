<?php

namespace OpenConext\EngineBlockBridge\Configuration;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;

final class EngineBlockConfiguration
{
    /**
     * @var array
     */
    private $configuration = array();

    public function __construct(array $configuration)
    {
        foreach ($configuration as $key => $value) {
            if (is_array($value)) {
                $this->configuration[$key] = new self($value);
            } else {
                $this->configuration[$key] = $value;
            }
        }
    }

    /**
     * @param string $path
     * @param null|mixed $default
     * @return null|mixed|EngineBlockConfiguration
     */
    public function get($path, $default = null)
    {
        if (!is_string($path) || trim($path) === '') {
            throw InvalidArgumentException::invalidType('non-empty string', 'path', $path);
        }

        $subPaths = explode('.', $path);
        $subPath = array_shift($subPaths);

        if (!array_key_exists($subPath, $this->configuration)) {
            return $default;
        }

        $value = $this->configuration[$subPath];

        if ($value instanceof EngineBlockConfiguration && !empty($subPaths)) {
            return $value->get(join('.', $subPaths), $default);
        }

        if (is_string($value)) {
            return str_replace('%%', '%', $value);
        }

        return $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = array();

        foreach ($this->configuration as $key => $value) {
            if ($value instanceof EngineBlockConfiguration) {
                $result[$key] = $value->toArray();
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Magic function so that $obj->value and related expression language will work
     * @param string $name
     * @return mixed|null|EngineBlockConfiguration
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}
