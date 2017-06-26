<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use InvalidArgumentException;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Response;
use OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\DataStore\AbstractDataStore;

final class FunctionalTestingAttributeAggregationClient implements AttributeAggregationClientInterface
{
    /**
     * @var AbstractDataStore
     */
    private $dataStore;

    /**
     * @var string
     */
    private $fixture;

    public function __construct(AbstractDataStore $dataStore)
    {
        $this->dataStore = $dataStore;
        $this->fixture = $dataStore->load();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function aggregate(Request $request)
    {
        if ($this->fixture === 'aggregate-orcid') {
            if (empty($request->rules)) {
                throw new InvalidArgumentException(
                    'Expecting an ARP rule for eduPersonOrcid, but no rules found.'
                );
            }

            $rule = reset($request->rules);
            if ($rule->name !== 'eduPersonOrcid' || $rule->source !== 'voot') {
                throw new InvalidArgumentException(
                    'Expectation failed for fixture aggregate-orcid: expecting ARP rule for eduPersonOrcid from source voot'
                );
            }

            return Response::fromData([
                [
                    'name' => 'eduPersonOrcid',
                    'values' => ['123456'],
                    'source' => 'voot',
                ],
            ]);
        }

        // Default to no empty aggregation response.
        return Response::fromData([]);
    }

    /**
     * Configure the client mock to return no attributes.
     */
    public function returnsNothing()
    {
        $this->dataStore->save('aggregate-nothing');
    }

    /**
     * Configure the client mock to return an eduPersonOrcid attribute.
     */
    public function returnsOrcid()
    {
        $this->dataStore->save('aggregate-orcid');
    }
}
