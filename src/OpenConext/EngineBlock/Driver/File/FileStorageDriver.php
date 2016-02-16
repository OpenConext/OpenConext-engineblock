<?php

namespace OpenConext\EngineBlock\Driver\File;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Driver\StorageDriver;

final class FileStorageDriver implements StorageDriver
{
    /**
     * @var FileHandler
     */
    private $fileHandler;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @param FileHandler $fileHandler
     * @param string      $filePath
     */
    public function __construct(FileHandler $fileHandler, $filePath)
    {
        Assertion::nonEmptyString($filePath, 'filePath');

        $this->fileHandler = $fileHandler;
        $this->filePath    = $filePath;
    }

    public function save($data)
    {
        Assertion::string($data, 'Data to save must be a string, "%s" given');

        $this->fileHandler->writeTo($data, $this->filePath);
    }

    public function load()
    {
        return $this->fileHandler->readFrom($this->filePath);
    }
}
