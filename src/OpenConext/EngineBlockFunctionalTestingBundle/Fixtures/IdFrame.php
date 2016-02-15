<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

/**
 * A 'frame' of ids, as in a set of ids for use in a single step of the EngineBlock flow.
 * @package OpenConext\EngineBlockFunctionalTestingBundle\Fixtures
 */
class IdFrame
{
    const ID_USAGE_SAML2_RESPONSE   = 'saml2-response';
    const ID_USAGE_SAML2_REQUEST    = 'saml2-request';
    const ID_USAGE_SAML2_ASSERTION  = 'saml2-assertion';
    const ID_USAGE_SAML2_METADATA   = 'saml2-metadata';
    const ID_USAGE_OTHER            = 'other';

    protected $ids;

    /**
     * @param array $ids
     */
    public function __construct($ids = array())
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
     * @throws \RuntimeException
     */
    public function get($usage)
    {
        $id = array_shift($this->ids[$usage]);
        if (!$id) {
            throw new \RuntimeException(
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
