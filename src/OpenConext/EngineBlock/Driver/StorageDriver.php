<?php

namespace OpenConext\EngineBlock\Driver;

interface StorageDriver
{
    /**
     * @param $data
     * @return boolean whether or not the data was saved succesfully
     * @throws \OpenConext\EngineBlock\Exception\Exception
     */
    public function save($data);

    /**
     * @return mixed
     * @throws \OpenConext\EngineBlock\Exception\Exception
     */
    public function load();
}
