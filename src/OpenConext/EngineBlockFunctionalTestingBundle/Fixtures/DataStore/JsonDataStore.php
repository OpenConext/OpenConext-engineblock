<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore;

class JsonDataStore extends AbstractDataStore
{
    protected function encode($data)
    {
        return json_encode($data);
    }

    protected function decode($data)
    {
        return json_decode($data, true);
    }
}
