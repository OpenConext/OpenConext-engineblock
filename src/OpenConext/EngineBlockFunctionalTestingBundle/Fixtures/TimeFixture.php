<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\JsonDataStore;

class TimeFixture
{
    protected $fixture;
    protected $time;

    public function __construct(JsonDataStore $fixture)
    {
        $this->fixture = $fixture;

        $this->load();
    }

    protected function load()
    {
        $time = $this->fixture->load(false);
        if ($time === false) {
            return;
        }

        $this->time = $time;
    }

    public function get()
    {
        if (!isset($this->time)) {
            return time();
        }

        return (int) $this->time;
    }

    public function set($time)
    {
        $this->time = (string) (int) $time;
    }

    public function __destruct()
    {
        if (!isset($this->time)) {
            return;
        }

        $this->fixture->save($this->time);
    }
}
