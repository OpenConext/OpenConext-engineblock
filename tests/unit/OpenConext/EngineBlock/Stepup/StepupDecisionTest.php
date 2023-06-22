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
use Psr\Log\LoggerInterface;
use function reset;

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
        $logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();

        $stepupDecision = new StepupDecision($idp, $sp, $input[2], $input[3], $repo, $logger);

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
                ->andReturn(Loa::create((int)substr($input[1],-2), $input[1]))
            ;
        }

        // Now set the SP expectation
        if (is_string($input[0]) && !empty($input[0])) {
            $repo
                ->shouldReceive('getByIdentifier')
                ->with($input[0])
                ->andReturn(Loa::create((int)substr($input[0],-2), $input[0]))
            ;
        }

        if (is_array($input[3]) && !empty($input[3])) {
            foreach($input[3] as $loa) {
                $repo
                    ->shouldReceive('getByIdentifier')
                    ->with($loa)
                    ->andReturn(Loa::create((int)substr($loa,-2), $loa))
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
            'Use SP LoA if only SP LoA set (do not allow no stepup)' => [['loa20', '', [], [], false], [true, 'loa20', false]],
            'Use IdP LoA if only IdP LoA set (do not allow no stepup)' => [['', 'loa30', [], [], false], [true, 'loa30', false]],

            'Use no stepup if no coins set for IdP and SP (allow no stepup)' => [[null, null, [], [], true], [false, '', false]],
            'Use no stepup if coins empty for IdP and SP (allow no stepup)' => [['', '', [], [], true], [false, '', false]],
            'Use SP LoA if only SP LoA set (allow no stepup)' => [['loa20', '', [], [], true], [true, 'loa20', true]],
            'Use IdP LoA if only IdP LoA set (allow no stepup)' => [['', 'loa30', [], [], true], [true, 'loa30', true]],

            'Use SP LoA if SP LoA is highest (allow no stepup)' => [['loa30', 'loa20', [], [], true], [true, 'loa30', true]],
            'Use IdP LoA if IdP LoA is highest (allow no stepup)' => [['loa20', 'loa30', [], [], true], [true, 'loa30', true]],

            'Use PdP LoA if SP LoA and PdP LoA set and PDP LoA is highest (allow no stepup)' => [['loa20', '', [], ['loa30'], true], [true, 'loa30', true]],
            'Use IdP LoA if IdP LoA is highest and PdP LoA set (allow no stepup)' => [['', 'loa30', [], ['loa20'], true], [true, 'loa30', true]],
            'Use PdP LoA if PdP LoA set (allow no stepup)' => [['', '', [], ['loa30'], true], [true, 'loa30', true]],
            'Use highest PdP LoA if multiple PdP LoA set (allow no stepup)' => [['', '', [], ['loa30', 'loa20'], true], [true, 'loa30', true]],
            'Use highest PdP LoA if multiple PdP LoA set in different order (allow no stepup)' => [['', '', [], ['loa30', 'loa20'], true], [true, 'loa30', true]],

            'Allow AuthnRequest (SP) LoA1, but take no action' => [['', '', [$this->buildLoa('loa10')], [], true], [false, 'loa10', false]],
            'Use AuthnRequest (SP) LoA if SP LoA is lower' => [['loa20', '', [$this->buildLoa('loa30')], [], true], [true, 'loa30', true]],
            'Use IdP LoA if IdP LoA is highest and AuthnRequest (SP) LoA set (allow no stepup)' => [['', 'loa30', [$this->buildLoa('loa20')], [], true], [true, 'loa30', true]],
            'Use PdP LoA if  AuthnRequest (SP) LoA set (allow no stepup)' => [['', '', [$this->buildLoa('loa30')], [], true], [true, 'loa30', true]],
            'Use highest AuthnRequest (SP) LoA if multiple LoA set (allow no stepup)' => [['', '', [$this->buildLoa('loa30'), $this->buildLoa('loa20')], [], true], [true, 'loa30', true]],
            'Use highest AuthnRequest (SP) LoA if multiple LoA set (authn + pdp) in different order (allow no stepup)' => [['', '', [$this->buildLoa('loa20'), $this->buildLoa('loa30')], ['loa20'], true], [true, 'loa30', true]],

            'Use highest LoA from many options (allow no stepup)' => [['loa20', 'loa30', [], ['loa20', 'loa20', 'loa30'], true], [true, 'loa30', true]],
            'Use highest LoA from many different options (allow no stepup)' => [['loa30', 'loa20', [$this->buildLoa('loa20'), $this->buildLoa('loa20'), $this->buildLoa('loa30')], [], true], [true, 'loa30', true]],
       ];
    }

    private function buildLoa(string $loaLevel): Loa
    {
        $matches = [];
        preg_match_all('/\d+/', $loaLevel, $matches);
        $level = (int) reset($matches[0]);
        $loa = Loa::create($level, $loaLevel);
        return $loa;
    }
}
