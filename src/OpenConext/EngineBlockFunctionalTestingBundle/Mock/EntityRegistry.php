<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
