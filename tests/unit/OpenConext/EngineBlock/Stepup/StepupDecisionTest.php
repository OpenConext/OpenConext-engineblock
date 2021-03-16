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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidStepupConfigurationException;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Loa;
use OpenConext\EngineBlock\Metadata\LoaRepository;
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
                'stepupAllowNoToken' => $input[3],
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

        $repo = $this->buildMockRepository($input);

        $stepupDecision = new StepupDecision($idp, $sp, $input[2], $repo);

        $useStepup = $stepupDecision->shouldUseStepup();
        $stepupLoa = $stepupDecision->getStepupLoa();
        // StepupLoa is either of type Loa or null, the test expectation verifies the identifier (string)
        if ($stepupLoa) {
            $stepupLoa = $stepupLoa->getIdentifier();
        }
        $allowNoToken = $stepupDecision->allowNoToken();

        $this->assertEquals($useStepup, $expectedResult[0]);
        $this->assertEquals($stepupLoa, $expectedResult[1]);
        $this->assertEquals($allowNoToken, $expectedResult[2]);
    }

    private function buildMockRepository($input)
    {
        $repo = m::mock(LoaRepository::class);

        // In the StepupDecision, the IdP is retrieved before the SP
        if (is_string($input[1]) && !empty($input[1])) {
            $repo
                ->shouldReceive('getByIdentifier')
                ->with($input[1])
                ->andReturn(Loa::create(2, $input[1]))
            ;
        }

        // Now set the SP expectation
        if (is_string($input[0]) && !empty($input[0])) {
            $repo
                ->shouldReceive('getByIdentifier')
                ->with($input[0])
                ->andReturn(Loa::create(2, $input[0]))
            ;
        }

        if (is_string($input[2]) && !empty($input[2])) {
            $repo
                ->shouldReceive('getByIdentifier')
                ->with($input[2])
                ->andReturn(Loa::create(2, $input[2]))
            ;
        }
        return $repo;
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

        $repo = $this->buildMockRepository($input);

        $sp = Utils::instantiate(
            ServiceProvider::class,
            [
                'entityId' => 'sp',
                'stepupRequireLoa' => $input[0],
                'stepupAllowNoToken' => $input[3],
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

        $stepupDecision = new StepupDecision($idp, $sp, $input[2], $repo);
    }


    public function stepupCoinsAndExpectedResultProvider()
    {
        return [
            'Use no stepup if no coins set for IdP and SP (do not allow no stepup)' => [[null, null, null, false], [false, '', false]],
            'Use no stepup if coins empty for IdP and SP (do not allow no stepup)' => [['', '', null, false], [false, '', false]],
            'Use SP LoA if only SP LoA set (do not allow no stepup)' => [['loa2', '', null, false], [true, 'loa2', false]],
            'Use IdP LoA if only IdP LoA set (do not allow no stepup)' => [['', 'loa3', null, false], [true, 'loa3', false]],

            'Use no stepup if no coins set for IdP and SP (allow no stepup)' => [[null, null, null, true], [false, '', false]],
            'Use no stepup if coins empty for IdP and SP (allow no stepup)' => [['', '', null, true], [false, '', false]],
            'Use SP LoA if only SP LoA set (allow no stepup)' => [['loa2', '', null, true], [true, 'loa2', true]],
            'Use IdP LoA if only IdP LoA set (allow no stepup)' => [['', 'loa3', null, true], [true, 'loa3', true]],

            'Use PdP LoA if SP LoA and PdP LoA set (allow no stepup)' => [['loa2', '', 'loa3', true], [true, 'loa3', true]],
            'Use PdP LoA if IdP LoA and PdP LoA set (allow no stepup)' => [['', 'loa3', 'loa2', true], [true, 'loa2', true]],
            'Use PdP LoA if PdP LoA set (allow no stepup)' => [['', '', 'loa3', true], [true, 'loa3', true]],
        ];
    }

    public function stepupCoinsExceptionProvider()
    {
        return [
            'Throw exception if both SP and IdP LoA set (unable to make decision)' => [['loa2', 'loa3', null, false]],
        ];
    }
}
