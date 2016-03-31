<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;

/**
 * Ids
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Fixtures
 */
class IdFixture
{
    const FRAME_REQUEST = 'request';
    const FRAME_RESPONSE = 'response';

    protected $dataStore;

    /**
     * @var IdFrame[]
     */
    protected $frames;

    /**
     * @param AbstractDataStore $dataStore
     */
    public function __construct(AbstractDataStore $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    private function loadFrames()
    {
        if (isset($this->frames)) {
            return;
        }

        $this->frames = $this->dataStore->load();
    }

    /**
     * Get the top frame off the queue for use.
     */
    public function shiftFrame()
    {
        $this->loadFrames();

        if (empty($this->frames)) {
            throw new \RuntimeException('No more IdFrames?');
        }
        return array_shift($this->frames);
    }

    public function hasFrame($frameName)
    {
        $this->loadFrames();

        return isset($this->frames[$frameName]);
    }

    public function getFrame($frameName)
    {
        $this->loadFrames();

        if (!isset($this->frames[$frameName])) {
            throw new \RuntimeException("No frame with given name '$frameName'");
        }
        return $this->frames[$frameName];
    }

    /**
     * Queue up another set of ids to use.
     *
     * @param $frameName
     * @param IdFrame $frame
     * @return $this
     */
    public function addFrame($frameName, IdFrame $frame)
    {
        $this->loadFrames();

        $this->frames[$frameName] = $frame;
        return $this;
    }

    /**
     * Remove all frames.
     */
    public function clear()
    {
        if (!isset($this->frames)) {
            return $this;
        }

        $this->loadFrames();

        $this->frames = [];
        return $this;
    }

    /**
     * On destroy write out the current state.
     */
    public function __destruct()
    {
        if (!isset($this->frames)) {
            return;
        }

        $this->dataStore->save($this->frames);
    }
}
