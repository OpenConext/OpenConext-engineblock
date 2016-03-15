<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use RuntimeException;

/**
 * A 'frame' of ids, as in a set of ids for use in a single step of the EngineBlock flow.
 */
class IdFrame
{
    protected $ids;

    /**
     * @param array $ids
     */
    public function __construct($ids = [])
    {
        $this->ids = $ids;
    }

    /**
     * @param $usage
     * @param $id
     * @return $this
     */
    public function set($usage, $id)
    {
        $this->ids[$usage][] = $id;

        return $this;
    }

    /**
     * @param $usage
     * @return mixed
     * @throws RuntimeException
     */
    public function get($usage)
    {
        $id = array_shift($this->ids[$usage]);
        if (!$id) {
            throw new RuntimeException(
                'Current frame has no id set for ' . $usage . ', available ids: ' . print_r($this->ids, true)
            );
        }

        return $id;
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->ids;
    }

    /**
     * @param $usage
     * @return bool
     */
    public function has($usage)
    {
        return isset($this->ids[$usage]);
    }
}
