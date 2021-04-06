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
                'stepupAllowNoToken' => $input[4],
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

        $stepupDecision = new StepupDecision($idp, $sp, $input[2], $input[3], $repo);

        $useStepup = $stepupDecision->shouldUseStepup();
        $stepupLoa = $stepupDecision->getStepupLoa();
        // StepupLoa is either of type Loa or null, the test expectation verifies the identifier (string)
        if ($stepupLoa) {
            $stepupLoa = $stepupLoa->getIdentifier();
        }
        $allowNoToken = $stepupDecision->allowNoToken();

        $this->assertEquals($expectedResult[0], $useStepup);
        $this->assertEquals($expectedResult[1], $stepupLoa);
        $this->assertEquals($expectedResult[2], $allowNoToken);
    }

    private function buildMockRepository($input)
    {
        $repo = m::mock(LoaRepository::class);

        // In the StepupDecision, the IdP is retrieved before the SP
        if (is_string($input[1]) && !empty($input[1])) {
            $repo
                ->shouldReceive('getByIdentifier')
                ->with($input[1])
                ->andReturn(Loa::create((int)substr($input[1],-1), $input[1]))
            ;
        }

        // Now set the SP expectation
        if (is_string($input[0]) && !empty($input[0])) {
            $repo
                ->shouldReceive('getByIdentifier')
                ->with($input[0])
                ->andReturn(Loa::create((int)substr($input[0],-1), $input[0]))
            ;
        }

        if (is_array($input[2]) && !empty($input[2])) {
            foreach($input[2] as $loa) {
                $repo
                    ->shouldReceive('getByIdentifier')
                    ->with($loa)
                    ->andReturn(Loa::create((int)substr($loa,-1), $loa))
                 ;
            }
        }
        if (is_array($input[3]) && !empty($input[3])) {
            foreach($input[3] as $loa) {
                $repo
                    ->shouldReceive('getByIdentifier')
                    ->with($loa)
                    ->andReturn(Loa::create((int)substr($loa,-1), $loa))
                 ;
            }
        }
        return $repo;
    }

    public function stepupCoinsAndExpectedResultProvider()
    {
        return [
            'Use no stepup if no coins set for IdP and SP (do not allow no stepup)' => [[null, null, [], [], false], [false, '', false]],
            'Use no stepup if coins empty for IdP and SP (do not allow no stepup)' => [['', '', [], [], false], [false, '', false]],
            'Use SP LoA if only SP LoA set (do not allow no stepup)' => [['loa2', '', [], [], false], [true, 'loa2', false]],
            'Use IdP LoA if only IdP LoA set (do not allow no stepup)' => [['', 'loa3', [], [], false], [true, 'loa3', false]],

            'Use no stepup if no coins set for IdP and SP (allow no stepup)' => [[null, null, [], [], true], [false, '', false]],
            'Use no stepup if coins empty for IdP and SP (allow no stepup)' => [['', '', [], [], true], [false, '', false]],
            'Use SP LoA if only SP LoA set (allow no stepup)' => [['loa2', '', [], [], true], [true, 'loa2', true]],
            'Use IdP LoA if only IdP LoA set (allow no stepup)' => [['', 'loa3', [], [], true], [true, 'loa3', true]],

            'Use SP LoA if SP LoA is higest (allow no stepup)' => [['loa3', 'loa2', [], [], true], [true, 'loa3', true]],
            'Use IdP LoA if IdP LoA is highest (allow no stepup)' => [['loa2', 'loa3', [], [], true], [true, 'loa3', true]],

            'Use PdP LoA if SP LoA and PdP LoA set and PDP LoA is highest (allow no stepup)' => [['loa2', '', [], ['loa3'], true], [true, 'loa3', true]],
            'Use IdP LoA if IdP LoA is highest and PdP LoA set (allow no stepup)' => [['', 'loa3', [], ['loa2'], true], [true, 'loa3', true]],
            'Use PdP LoA if PdP LoA set (allow no stepup)' => [['', '', [], ['loa3'], true], [true, 'loa3', true]],
            'Use highest PdP LoA if multiple PdP LoA set (allow no stepup)' => [['', '', [], ['loa3','loa2'], true], [true, 'loa3', true]],
            'Use highest PdP LoA if multiple PdP LoA set in different order (allow no stepup)' => [['', '', [], ['loa2','loa3'], true], [true, 'loa3', true]],

            'Use AuthnRequest (SP) LoA if SP LoA and PdP LoA set and PDP LoA is highest (allow no stepup)' => [['loa2', '', ['loa3'], [], true], [true, 'loa3', true]],
            'Use IdP LoA if IdP LoA is highest and AuthnRequest (SP) LoA set (allow no stepup)' => [['', 'loa3', ['loa2'], [], true], [true, 'loa3', true]],
            'Use PdP LoA if  AuthnRequest (SP) LoA set (allow no stepup)' => [['', '', ['loa3'], [], true], [true, 'loa3', true]],
            'Use highest AuthnRequest (SP) LoA if multiple LoA set (allow no stepup)' => [['', '', ['loa3','loa2'], [], true], [true, 'loa3', true]],
            'Use highest AuthnRequest (SP) LoA if multiple LoA set in different order (allow no stepup)' => [['', '', ['loa2','loa3'], [], true], [true, 'loa3', true]],
            'Use highest AuthnRequest (SP) LoA if multiple LoA set (authn + pdp) in different order (allow no stepup)' => [['', '', ['loa2','loa3'], ['loa2'], true], [true, 'loa3', true]],

            'Use highest LoA from many options (allow no stepup)' => [['loa2', 'loa3', [], ['loa2','loa2','loa3'], true], [true, 'loa3', true]],
            'Use highest LoA from many different options (allow no stepup)' => [['loa3', 'loa2', [], ['loa2','loa2','loa2'], true], [true, 'loa3', true]],
       ];
    }
}
