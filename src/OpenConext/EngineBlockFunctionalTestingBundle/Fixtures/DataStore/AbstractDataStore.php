<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use RuntimeException;

abstract class AbstractDataStore
{
    protected $filePath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $directory = dirname($this->filePath);
        $adapter = new Local($directory, LOCK_NB);
        $this->fileSystem = new Filesystem($adapter);
    }

    public function load($default = [])
    {
        $fileContents = $this->fileSystem->read($this->filePath);

        if ($fileContents === false) {
            throw new RuntimeException('Unable to load data from: ' . $this->filePath);
        }

        if (empty($fileContents)) {
            return $default;
        }

        $data = $this->decode($fileContents);
        if ($data === false) {
            throw new RuntimeException('Unable to decode data from: ' . $this->filePath);
        }

        return $data;
    }

    public function save($data)
    {
        $this->fileSystem->put($this->filePath, $this->encode($data));
    }

    abstract protected function encode($data);

    abstract protected function decode($data);
}
