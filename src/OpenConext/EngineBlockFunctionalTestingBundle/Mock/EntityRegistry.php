<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;
use Symfony\Component\HttpFoundation\ParameterBag;

class EntityRegistry extends ParameterBag
{
    /**
     * @var AbstractDataStore
     */
    private $dataStore;

    /**
     * @param AbstractDataStore $dataStore
     */
    public function __construct(AbstractDataStore $dataStore)
    {
        $this->dataStore = $dataStore;

        parent::__construct($dataStore->load());
    }

    /**
     * @return MockIdentityProvider|MockServiceProvider
     * @throws \RuntimeException
     */
    public function getOnly()
    {
        $count = $this->count();

        if ($count === 0) {
            throw new \RuntimeException("No entities registered yet (use before definition)");
        }

        if ($count !== 1) {
            throw new \RuntimeException("More than 1 entities registered, unable to get a single entity");
        }

        return $this->getIterator()->current();
    }

    public function clear()
    {
        $this->parameters = [];

        return $this;
    }

    public function save()
    {
        $this->dataStore->save($this->parameters);

        return $this;
    }

    public function __destruct()
    {
        $this->save();
    }
}
