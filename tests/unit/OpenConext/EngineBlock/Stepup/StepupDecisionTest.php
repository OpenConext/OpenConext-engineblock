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

namespace OpenConext\EngineBlockBundle\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidStepupConfigurationException;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\StepupConnections;
use OpenConext\EngineBlock\Metadata\Utils;
use OpenConext\EngineBlock\Stepup\StepupDecision;
use PHPUnit\Framework\TestCase;

class StepupDecisionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group Stepup
     * @dataProvider stepupCoinsAndExpectedResultProvider
     *
     * @param array $input
     * @param array $expectedResult
     */
    public function the_correct_stepup_decision_should_be_made_based_on_a_coin_data(
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

        $stepupDecision = new StepupDecision($idp, $sp);

        $useStepup = $stepupDecision->shouldUseStepup();
        $stepupLoa = $stepupDecision->getStepupLoa();
        $allowNoToken = $stepupDecision->allowNoToken();

        $this->assertEquals($useStepup, $expectedResult[0]);
        $this->assertEquals($stepupLoa, $expectedResult[1]);
        $this->assertEquals($allowNoToken, $expectedResult[2]);
    }

    /**
     * @test
     * @group Stepup
     * @dataProvider stepupCoinsExceptionProvider
     *
     * @param array $input
     */
    public function invalid_input_for_stepup_decision_should_throw_exception(
        $input
    ) {

        $this->expectException(InvalidStepupConfigurationException::class);
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

        $stepupDecision = new StepupDecision($idp, $sp);
    }


    public function stepupCoinsAndExpectedResultProvider()
    {
        return [
            'Use no stepup if no coins set for IdP and SP (do not allow no stepup)' => [[null, null, false], [false, '', false]],
            'Use no stepup if coins empty for IdP and SP (do not allow no stepup)' => [['', '', false], [false, '', false]],
            'Use SP LoA if only SP LoA set (do not allow no stepup)' => [['loa2', '', false], [true, 'loa2', false]],
            'Use IdP LoA if only IdP LoA set (do not allow no stepup)' => [['', 'loa3', false], [true, 'loa3', false]],

            'Use no stepup if no coins set for IdP and SP (allow no stepup)' => [[null, null, true], [false, '', false]],
            'Use no stepup if coins empty for IdP and SP (allow no stepup)' => [['', '', true], [false, '', false]],
            'Use SP LoA if only SP LoA set (allow no stepup)' => [['loa2', '', true], [true, 'loa2', true]],
            'Use IdP LoA if only IdP LoA set (allow no stepup)' => [['', 'loa3', true], [true, 'loa3', true]],
        ];
    }

    public function stepupCoinsExceptionProvider()
    {
        return [
            'Throw exception if both SP and IdP LoA set (unable to make decision)' => [['loa2', 'loa3', false]],
        ];
    }
}
