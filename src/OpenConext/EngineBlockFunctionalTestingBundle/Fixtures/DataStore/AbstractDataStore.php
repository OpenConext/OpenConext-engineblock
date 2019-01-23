<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore;

use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractDataStore
{
    protected $filePath;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct(Filesystem $fileSystem, $filePath)
    {
        $this->filePath = $filePath;
        $this->fileSystem = $fileSystem;
    }

    protected function verifyFilePath()
    {
        if (is_writeable($this->filePath)) {
            return;
        }

        $directory = dirname($this->filePath);
        if (!file_exists($directory)) {
            $createdDirectory = mkdir($directory, 0777, true);
            if (!$createdDirectory) {
                throw new \RuntimeException('Unable to create directory: ' . $directory);
            }
        }

        if (!file_exists($this->filePath)) {
            $touched = touch($this->filePath);
            chmod($this->filePath, 0666);
            if (!$touched) {
                throw new \RuntimeException('Unable to create file: ' . $this->filePath);
            }
        }
    }

    public function load($default = [])
    {
        $this->verifyFilePath();
        $fileContents = file_get_contents($this->filePath);
        if ($fileContents === false) {
            throw new \RuntimeException('Unable to load data from: ' . $this->filePath);
        }
        if (empty($fileContents)) {
            return $default;
        }

        $data = $this->decode($fileContents);
        if ($data === false) {
            throw new \RuntimeException('Unable to decode data from: ' . $this->filePath);
        }

        return $data;
    }

    public function save($data)
    {
        $this->verifyFilePath();

        $this->fileSystem->dumpFile($this->filePath, $this->encode($data));
    }

    abstract protected function encode($data);

    abstract protected function decode($data);
}
