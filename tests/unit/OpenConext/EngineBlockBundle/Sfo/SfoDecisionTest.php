<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Tests;

use OpenConext\EngineBlock\Exception\InvalidSfoConfigurationException;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\StepupConnections;
use OpenConext\EngineBlock\Metadata\Utils;
use OpenConext\EngineBlockBundle\Sfo\SfoDecision;
use PHPUnit_Framework_TestCase as TestCase;

class SfoDecisionTest extends TestCase
{
    /**
     * @test
     * @group Sfo
     * @dataProvider sfoCoinsAndExpectedResultProvider
     *
     * @param array $input
     * @param array $expectedResult
     */
    public function the_correct_sfo_decision_should_be_made_based_on_a_coin_data(
        $input,
        $expectedResult
    ) {

        $sp = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'sp',
                'stepupRequireLoa' => $input[0],
                'stepupAllowNoToken' => $input[2],
            ]
        );
        $idp = Utils::instantiate(
            IdentityProvider::class,
            [
                'entityId' => 'idp',
                'stepupConnections' => new StepupConnections([
                    'sp' => $input[1],
                ]),
            ]
        );

        $sfoDecision = new SfoDecision($idp, $sp);

        $useSfo = $sfoDecision->shouldUseSfo();
        $sfoLoa = $sfoDecision->getSfoLoa();
        $allowNoToken = $sfoDecision->allowNoToken();

        $this->assertEquals($useSfo, $expectedResult[0]);
        $this->assertEquals($sfoLoa, $expectedResult[1]);
        $this->assertEquals($allowNoToken, $expectedResult[2]);
    }

    /**
     * @test
     * @group Sfo
     * @dataProvider sfoCoinsExceptionProvider
     *
     * @param array $input
     */
    public function invalid_input_for_sfo_decision_should_throw_exception(
        $input
    ) {

        $this->expectException(InvalidSfoConfigurationException::class);
        $this->expectExceptionMessage('Both IdP "idp" and SP "sp" where configured to use stepup authentication. This is not allowed');

        $sp = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'sp',
                'stepupRequireLoa' => $input[0],
                'stepupAllowNoToken' => $input[2],
            ]
        );
        $idp = Utils::instantiate(
            IdentityProvider::class,
            [
                'entityId' => 'idp',
                'stepupConnections' => new StepupConnections([
                    'sp' => $input[1],
                ]),
            ]
        );

        $sfoDecision = new SfoDecision($idp, $sp);
    }


    public function sfoCoinsAndExpectedResultProvider()
    {
        return [
            'Use no SFO if no coins set for IDP and SP (do not allow no sfo)' => [[null, null, false], [false, '', false]],
            'Use no SFO if coins empty for IDP and SP (do not allow no sfo)' => [['', '', false], [false, '', false]],
            'Use SP loa if only SP loa set (do not allow no sfo)' => [['loa2', '', false], [true, 'loa2', false]],
            'Use IDP loa if only IDP loa set (do not allow no sfo)' => [['', 'loa3', false], [true, 'loa3', false]],

            'Use no SFO if no coins set for IDP and SP (allow no sfo)' => [[null, null, true], [false, '', false]],
            'Use no SFO if coins empty for IDP and SP (allow no sfo)' => [['', '', true], [false, '', false]],
            'Use SP loa if only SP loa set (allow no sfo)' => [['loa2', '', true], [true, 'loa2', true]],
            'Use IDP loa if only IDP loa set (allow no sfo)' => [['', 'loa3', true], [true, 'loa3', true]],
        ];
    }

    public function sfoCoinsExceptionProvider()
    {
        return [
            'Throw exception if both SP and IDP loa set (unable to make decision)' => [['loa2', 'loa3', false]],
        ];
    }
}
