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

namespace OpenConext\EngineBlock\Stepup;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\RuntimeException;

class StepupGatewayLoaMapping
{
    private $mapping = [];
    private $gatewayLoa1 = '';

    /**
     * @param array $loaMapping
     * @param string $gatewayLoa1
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(array $loaMapping, $gatewayLoa1)
    {
        foreach ($loaMapping as $from => $to) {
            Assertion::string($from, 'The stepup.gateway_loa_mapping configuration must be a map, key is not a string');
            Assertion::string($to, 'The stepup.gateway_loa_mapping configuration must be a map, value is not a string');

            $this->mapping[$from] = $to;
        }

        Assertion::string($gatewayLoa1, 'The stepup.gateway.loa.loa1 configuration must be a string');

        $this->gatewayLoa1 = $gatewayLoa1;
    }

    /**
     * @param $input
     * @return string
     */
    public function transformToGatewayLoa($input)
    {
        if (!array_key_exists($input, $this->mapping)) {
            throw new RuntimeException('Unable to find the EngineBlock LoA in the configured stepup LoA mapping');
        }

        return $this->mapping[$input];
    }


    /**
     * @param $input
     * @return string
     */
    public function transformToEbLoa($input)
    {
        $loa = array_search($input, $this->mapping);
        if ($loa === false) {
            throw new RuntimeException('Unable to find the received stepup LoA in the configured EngineBlock LoA');
        }

        return $loa;
    }

    /**
     * @return string
     */
    public function getGatewayLoa1()
    {
        return $this->gatewayLoa1;
    }
}
