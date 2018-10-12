<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore;

abstract class AbstractDataStore
{
    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
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
print_r(json_decode($fileContents));

        return $data;
    }

    public function save($data)
    {
        $this->verifyFilePath();

        file_put_contents($this->filePath, $this->encode($data));
    }

    abstract protected function encode($data);

    abstract protected function decode($data);
}
