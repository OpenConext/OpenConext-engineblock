<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\JsonDataStore;

class SuperGlobalsFixture
{
    const SERVER = 'SERVER';

    protected $fixture;
    protected $data = array();

    public function __construct(JsonDataStore $fixture)
    {
        $this->fixture = $fixture;
        $this->data = $this->fixture->load();
    }

    public function getAll()
    {
        return $this->data;
    }

    public function get($superGlobal)
    {
        return $this->data[$superGlobal];
    }

    public function set($superGlobal, $name, $value)
    {
        if (!isset($this->data[$superGlobal])) {
            $this->data[$superGlobal] = array();
        }

        $this->data[$superGlobal][$name] = $value;
    }

    public function __destruct()
    {
        $this->fixture->save($this->data);
    }
}
